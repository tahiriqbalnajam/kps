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
use Illuminate\Support\Facades\DB;
use App\Models\SmsQueue;

class StudentAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $class_id = $request->class;
        $date = $request->month;
        $start_month = Carbon::createFromFormat('Y-m-d', $date)->firstOfMonth()->format('Y-m-d');
        $end_month = Carbon::createFromFormat('Y-m-d', $date)->lastOfMonth()->format('Y-m-d');
        $attendance = StudentAttendance::where('class_id', $class_id)
                                        ->whereBetween('attendance_date',array($start_month,$end_month))
                                        ->orderBy('student_id')
                                        ->get();

        $students = Student::with(['attend' => function($query) use($class_id, $start_month, $end_month){
            return $query->where('class_id', $class_id)->whereBetween('attendance_date',array($start_month,$end_month));
        }])->where('class_id', $class_id)->select('roll_no','name','id')->get();

        // $period = CarbonPeriod::create($start_month, $end_month);
        // $attend = array();
        // $students->map( function($student) use($attendance, $period, $attend) {
        //     $attend['student_name'] = $student->name;
        //     foreach ($period as $date) {
        //         $record = $attendance->where('student_id', $student->id)->shift();
        //         print_r($record);
        //         if($record)
        //             $attend['attendance'][] = $record->status;
        //         else
        //             $attend['attendance'][] = '-';
        //     }
        // });
        
        // Iterate over the period
        
        
        return response()->json(new JsonResponse(['students' => $students]));

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
        $students = $request->students;
        foreach($students as $data){
            $attendance[] = [
                'class_id' => $data['class_id'],
                'student_id' => $data['id'],
                'status' => $data['attendance'],
                'attendance_date' =>  $date,
            ];

          //  if($data['attendance'] == 'Absent') {
          //      $sms['student_id'] = $data['id'];
          //      $sms['message'] = 'Dear, '.$data['parents']['name'].',  Your child \''.$data['name'].'\' is absent today';
          //      $sms['phone'] = $this->format_phone($data['parents']['phone']);
          //      SmsQueue::create($sms);
         //   }
        }
        StudentAttendance::insert($attendance);
    }


    function format_phone($phone) {
        $phone = ltrim($phone, '0'); //remove 0 from start
        $phone = ((strpos( $phone, "92" ) === 0)) ? $phone : '92'.$phone;
        $find = array(' ', '-');
        $replace = array('','');
        $phone = str_replace($find, $replace, $phone);
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
        ) AS female_absent
        
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
}
