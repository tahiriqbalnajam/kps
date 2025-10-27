<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Parents;
use App\Models\Student;
use Carbon\Carbon;
use App\Models\ClassSession;
use Carbon\CarbonPeriod;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Models\Settings;
use Illuminate\Support\Facades\DB;
use App\Models\SmsQueue;
use App\Services\Contracts\AttendanceServiceInterface;

class StudentAttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceServiceInterface $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $class_id = $request->stdclass;
        $date = $request->month;
        $start_month = Carbon::createFromFormat('Y-m-d', $date)->firstOfMonth()->format('Y-m-d');
        $end_month = Carbon::createFromFormat('Y-m-d', $date)->lastOfMonth()->format('Y-m-d');
       
        $attendance = StudentAttendance::where('class_id', $class_id)
                                        ->whereBetween('attendance_date',array($start_month,$end_month))
                                        ->orderBy('student_id')
                                        ->toSql();        
        
         return response()->json(new JsonResponse(['attendance' => $attendance]));

    }

    public function absent_student_each_class(Request $request) {
        $data = $request->all();
        $data = $this->attendanceService->absent_student_each_class($data);
        return response()->json(new JsonResponse(['class_student' => $data]));
    }

    public function get_att_comment($student_id) {
        $data = $this->attendanceService->get_student_attendace_comments($student_id);
        return response()->json(new JsonResponse(['comments' => $data]));

    }

    public function absent_comment(Request $request) {
        $data = $request->all();
        $data = $this->attendanceService->absent_comment($data);
        return response()->json(new JsonResponse(['attendance' => $data]));

    }


    public function student_attendance_marked(Request $request) {
        $search = $request->all();
        $attendance = $this->attendanceService->student_attendance_marked($search);
        return response()->json(new JsonResponse(['attendance' => $attendance]));
    }


    public function student_daily_classwise_attendance_report(Request $request) {
        $search = $request->all();
        $attendance = $this->attendanceService->student_daily_classwise($search);
        return response()->json(new JsonResponse(['attendance' => $attendance]));
    }

    public function student_monthly_attendance_report(Request $request) {
        $search = $request->all();
        $attendance = $this->attendanceService->student_monthly_attendance_report($search);
        return response()->json(new JsonResponse(['students' => $attendance]));
    }

    public function student_attendance_total($student_id) {
        $attendance = $this->attendanceService->student_attendance_total($student_id);
        return response()->json(new JsonResponse(['attendance' => $attendance]));
    }
    
    public function attendance_student_monthly(Request $request) {
        $student_id = $request->student_id;
        $date = $request->month;
        $start_month = Carbon::createFromFormat('Y-m-d', $date)->firstOfMonth()->format('Y-m-d');
        $end_month = Carbon::createFromFormat('Y-m-d', $date)->lastOfMonth()->format('Y-m-d');
        $attendance = StudentAttendance::where('student_id', $student_id)
                                        ->whereBetween('attendance_date',array($start_month,$end_month))
                                        ->orderBy('attendance_date')
                                        ->get();
        return response()->json(new JsonResponse(['attendance' => $attendance]));
    }

    public function student_att_report(Request $request) {
        $student_id = $request->student_id;
        if($student_id == null)
            return response()->json(new JsonResponse(['attendance' => []]));
        
        $attendance = DB::SELECT("SELECT student_id, DATE_FORMAT(attendance_date,'%m-%Y') as month, COUNT(student_id) as absent 
                                    FROM `student_attendances` 
                                    WHERE status = 'absent' AND attendance_date > now() - INTERVAL 12 month  AND student_id = ".$student_id."
                                    GROUP BY month, student_id;");
        return response()->json(new JsonResponse(['attendance' => $attendance]));
    }

    /**
     * Show the form for creating a new resource.
     *[] 
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $date = $request->date;
        $stdclass = $request->stdclass;
        
        // Check if the date is Sunday
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        if ($dayOfWeek === Carbon::SUNDAY) {
            return response()->json([
                'message' => 'Attendance cannot be taken on Sundays.',
                'errors' => [
                    'date' => ['Attendance is not allowed on Sundays.']
                ]
            ], 422);
        }
        StudentAttendance::where(['attendance_date'=> $date, 'class_id' => $stdclass])->delete();
        $students = $request->students;

        // Fetch relevant settings once before the loop
        $settingsCollection = Settings::whereIn('setting_key', [
            'school_name', 
            'address', 
            'phone', 
            'absent_sms_template',
            'message_channel'
        ])->pluck('setting_value', 'setting_key');

        $schoolName = $settingsCollection->get('school_name', 'Your School'); // Default if not set
        $schoolAddress = $settingsCollection->get('address', '');
        $schoolPhone = $settingsCollection->get('phone', '');
        $absentSmsTemplate = $settingsCollection->get('absent_sms_template', "Dear [[parent_name]], your child [[student_name]] (Class [[class_title]]) is absent today. Please ensure attendance. Thank you. - [[school_name]]"); // Default template
        $messageChannel = $settingsCollection->get('message_channel', 'sms'); // Default channel

        $attendance = [];
        foreach($students as $data){
            $attendance[] = [
                'class_id' => $data['class_id'],
                'student_id' => $data['id'],
                'status' => $data['attendance'],
                'attendance_date' =>  $date,
            ];
            
           if(strtolower($data['attendance']) == 'absent') {
               $sms = []; // Initialize sms array here
               $sms['student_id'] = $data['id'];
               $sms['channel'] = $messageChannel; // Use channel from settings

               // Prepare data for placeholder replacement
               $parentName = $data['parents']['name'] ?? 'Parent';
               $studentName = $data['name'] ?? 'Student';
               $className = $data['stdclasses']['name'] ?? 'N/A';

               // Replace placeholders in the template
               $message = str_replace(
                   ['[[parent_name]]', '[[student_name]]', '[[class_title]]', '[[school_name]]', '[[school_address]]', '[[school_phone]]'],
                   [$parentName, $studentName, $className, $schoolName, $schoolAddress, $schoolPhone],
                   $absentSmsTemplate
               );

               $sms['message'] = $message; // Assign the processed message
               $sms['phone'] = isset($data['parents']['phone']) ? $this->format_phone($data['parents']['phone']) : null;
               
               // Only create SMS queue if phone number is valid
               if ($sms['phone']) {
                   SmsQueue::create($sms);
               }
           }
        }
        // Use insert for better performance if attendance array is not empty
        if (!empty($attendance)) {
            StudentAttendance::insert($attendance);
        }

        // Consider returning a response, e.g., success message
        return response()->json(new JsonResponse(['message' => 'Attendance recorded successfully.']));
    }


    function format_phone($phone) {
        if (empty($phone)) {
            return null; // Return null for empty phone numbers
        }
        $phone = preg_replace('/[^0-9]/', '', $phone); // Remove non-numeric characters
        $phone = ltrim($phone, '0'); //remove 0 from start if exists
        // Add 92 prefix if it doesn't start with 92 and has a reasonable length (e.g., 10 digits for Pakistan mobile)
        if (strlen($phone) === 10 && strpos($phone, "92") !== 0) {
             $phone = '92' . $phone;
        } else if (strlen($phone) !== 12 || strpos($phone, "92") !== 0) {
            // Basic validation: if it's not 12 digits starting with 92 after formatting, consider it invalid for this context
             // Log::warning("Invalid phone format after processing: " . $phone); // Optional: Log invalid formats
             return null; 
        }
       
        return $phone;
    }

    function dailyclasswise(Request $request) {
        $date = $request->attendance_date;

        $attendance = (DB::SELECT("SELECT c.name,
        (select COUNT(s.id) from students s
        WHERE c.id = s.class_id
        GROUP by c.id
        ) AS total_student,
        (select COUNT(s.id) from students s
        WHERE c.id = s.class_id AND s.gender = 'female'
        GROUP by c.id
        ) AS total_female,
        (select COUNT(s.id) from students s
        WHERE c.id = s.class_id AND s.gender = 'male'
        GROUP by c.id
        ) AS total_male,
        (select COUNT(s.id) from students s
         LEFT JOIN student_attendances sa 
         ON sa.student_id = s.id
        WHERE c.id = s.class_id AND s.id = sa.student_id AND sa.status = 'present' AND  sa.attendance_date = date('$date')
        GROUP by c.id
        ) AS total_present,
        (select COUNT(s.id) from students s
         LEFT JOIN student_attendances sa 
         ON sa.student_id = s.id
        WHERE c.id = s.class_id AND s.id = sa.student_id AND s.gender = 'male' AND sa.status = 'present' AND  sa.attendance_date = date('$date')
        GROUP by c.id
        ) AS male_present,
        (select COUNT(s.id) from students s
         LEFT JOIN student_attendances sa 
         ON sa.student_id = s.id
        WHERE c.id = s.class_id AND s.id = sa.student_id AND s.gender = 'female' AND sa.status = 'present' AND  sa.attendance_date = date('$date')
        GROUP by c.id
        ) AS female_present,
        (select COUNT(s.id) from students s
         LEFT JOIN student_attendances sa 
         ON sa.student_id = s.id
        WHERE c.id = s.class_id AND s.id = sa.student_id AND sa.status = 'absent' AND  sa.attendance_date = date('$date')
        GROUP by c.id
        ) AS total_absent,
        (select COUNT(s.id) from students s
         LEFT JOIN student_attendances sa 
         ON sa.student_id = s.id
        WHERE c.id = s.class_id AND s.id = sa.student_id AND s.gender = 'male' AND sa.status = 'absent' AND  sa.attendance_date = date('$date')
        GROUP by c.id
        ) AS male_absent,
        (select COUNT(s.id) from students s
         LEFT JOIN student_attendances sa 
         ON sa.student_id = s.id
        WHERE c.id = s.class_id AND s.id = sa.student_id AND s.gender = 'female' AND sa.status = 'absent' AND  sa.attendance_date = date('$date')
        GROUP by c.id
        ) AS female_absent,

        (select COUNT(s.id) from students s
         LEFT JOIN student_attendances sa 
         ON sa.student_id = s.id
        WHERE c.id = s.class_id AND s.id = sa.student_id AND sa.status = 'leave' AND  sa.attendance_date = date('$date')
        GROUP by c.id
        ) AS total_onleave,
        (select COUNT(s.id) from students s
         LEFT JOIN student_attendances sa 
         ON sa.student_id = s.id
        WHERE c.id = s.class_id AND s.id = sa.student_id AND s.gender = 'male' AND sa.status = 'leave' AND  sa.attendance_date = date('$date')
        GROUP by c.id
        ) AS male_onleave,
        (select COUNT(s.id) from students s
         LEFT JOIN student_attendances sa 
         ON sa.student_id = s.id
        WHERE c.id = s.class_id AND s.id = sa.student_id AND s.gender = 'female' AND sa.status = 'leave' AND  sa.attendance_date = date('$date')
        GROUP by c.id
        ) AS female_onleave
        
        FROM classes c"));

        return response()->json(new JsonResponse(['attendance' => $attendance]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\StudentAttendance  $studentAttendance
     * @return \Illuminate\Http\Response
     */
    public function show(StudentAttendance $studentAttendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StudentAttendance  $studentAttendance
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentAttendance $studentAttendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StudentAttendance  $studentAttendance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudentAttendance $studentAttendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StudentAttendance  $studentAttendance
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentAttendance $studentAttendance)
    {
        //
    }

    public function get_attendance_summry(Request $request) {
        $month = $request->month;
        $summary = $this->attendanceService->get_attendance_summry($month);
        return response()->json(new JsonResponse(['summary' => $summary]));
    }

    /**
     * Get daily attendance data for graph
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getDailyAttendanceGraph(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        
        // Create an array of all dates in the range
        $period = CarbonPeriod::create($startDate, $endDate);
        $dates = [];
        $presentCount = [];
        $absentCount = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $dates[] = $date->format('M d');

            // Count present students for this date
            $present = StudentAttendance::where('attendance_date', $formattedDate)
                ->where('status', 'present')
                ->count();
            $presentCount[] = $present;

            // Count absent students for this date
            $absent = StudentAttendance::where('attendance_date', $formattedDate)
                ->where('status', 'absent')
                ->count();
            $absentCount[] = $absent;
        }

        $attendanceData = [
            'dates' => $dates,
            'present' => $presentCount,
            'absent' => $absentCount,
        ];

        return response()->json(new JsonResponse(['attendanceData' => $attendanceData]));
    }
}
