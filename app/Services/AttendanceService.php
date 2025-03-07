<?php
namespace App\Services;

use DB;
use Carbon\Carbon;
use App\Models\Classes;
use App\Models\Holiday;
use App\Models\Student;
use Carbon\CarbonPeriod;
use App\Models\StudentAttendance;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\Contracts\AttendanceServiceInterface;

class AttendanceService implements AttendanceServiceInterface
{
    public function getMonthlyAttendance($student_id, $date)
    {
        $start_month = Carbon::createFromFormat('Y-m-d', $date)->firstOfMonth()->format('Y-m-d');
        $end_month = Carbon::createFromFormat('Y-m-d', $date)->lastOfMonth()->format('Y-m-d');
        
        $attendance = StudentAttendance::where('student_id', $student_id)
                                        
                                        ->whereBetween('attendance_date', [$start_month, $end_month])
                                        ->orderBy('attendance_date')
                                        ->get();
        
        return $attendance;
    }

    public function get_student_attendance($student_id, $date)
    {
        $attendance = StudentAttendance::with('students')
                                        ->where('student_id', $student_id)
                                        ->where('attendance_date', $date)
                                        ->first();
        
        return $attendance;
    }

    public function student_monthly_attendance_report(array $request) {
        
        $start_month = Carbon::createFromFormat('Y-m-d', $request['month'])->firstOfMonth()->format('Y-m-d');
        $end_month = Carbon::createFromFormat('Y-m-d', $request['month'])->lastOfMonth()->format('Y-m-d');
        $class_id = $request['class'];

        $attendance = DB::select("
          SELECT
                s.id AS student_id,
                s.name AS student_name,
                c.date,
                CASE 
                    WHEN h.holiday_date IS NOT NULL THEN h.description
                    WHEN DAYOFWEEK(c.date) = 1 THEN 'Sun' -- 1 for Sunday in MySQL
                    WHEN a.id IS NOT NULL THEN 'P'
                    WHEN a.id IS NULL AND c.date <= CURRENT_DATE THEN 'A'
                    ELSE '-'
                END AS attendance_status
            FROM
                students s
            CROSS JOIN
                calendar c
            LEFT JOIN
                student_attendances a ON s.id = a.student_id AND c.date = a.attendance_date
            LEFT JOIN
                holidays h ON c.date = h.holiday_date
            WHERE
                c.date BETWEEN ? AND ?
                AND s.class_id = ?
            ORDER BY
                s.id, c.date;
        ", [$start_month, $end_month, $class_id]);

        foreach($attendance as $attend) {
            $students[$attend->student_id]['name'] = $attend->student_name;
            $students[$attend->student_id]['attendances'][] = $attend->attendance_status;
        }
        //print_r($attend);
        return  $students;
    }

    public function student_attendance_marked(array $request)
    {
        $date = $request['month'];
        $stdclass = $request['stdclass'];
        $attendance = StudentAttendance::where(['attendance_date'=>$date, 'class_id' => $stdclass])->get();
        
        return $attendance;
    }

    public function student_daily_classwise(array $request)
    {
        $date = $request['attendance_date'];
        $pef_filter = ($request['pef_admission'] == 'yes') ? 1 : 0;
        $query = DB::table('students as s')
            ->selectRaw('
                cl.name AS class_title,
                COUNT(DISTINCT s.id) AS total_students,
                COUNT(DISTINCT CASE WHEN s.gender = "male" THEN s.id END) AS total_male,
                COUNT(DISTINCT CASE WHEN s.gender = "female" THEN s.id END) AS total_female,
                COUNT(CASE WHEN s.gender = "male" AND sa.status = "present" THEN 1 END) AS total_male_present,
                COUNT(CASE WHEN s.gender = "male" AND (sa.status = "absent" OR sa.status = "leave") THEN 1 END) AS total_male_absent,
                COUNT(CASE WHEN s.gender = "female" AND sa.status = "present" THEN 1 END) AS total_female_present,
                COUNT(CASE WHEN s.gender = "female" AND (sa.status = "absent" OR sa.status = "leave") THEN 1 END) AS total_female_absent,
                COUNT(CASE WHEN sa.status = "present" THEN 1 END) AS total_present,
                COUNT(CASE WHEN (sa.status = "absent" OR sa.status = "leave") THEN 1 END) AS total_absent,
                ROUND((COUNT(CASE WHEN sa.status = "present" THEN 1 END) / COUNT(sa.id)) * 100 , 0) AS present_percentage,
                ROUND((COUNT(CASE WHEN (sa.status = "absent" OR sa.status = "leave") THEN 1 END) / COUNT(sa.id)) * 100 , 0) AS absent_percentage
            ')
            ->join('student_attendances as sa', function($join) {
                $join->on('s.id', '=', 'sa.student_id')
                    ->on('s.class_id', '=', 'sa.class_id');
            })
            ->join('classes as cl', 's.class_id', '=', 'cl.id')
            ->leftJoin('calendar as c', 'sa.attendance_date', '=', 'c.date')
            ->leftJoin('holidays as h', 'sa.attendance_date', '=', 'h.holiday_date')
            ->whereNull('h.holiday_date')
            ->where('c.date', $date);

        if ($pef_filter) {
            $query->whereRaw(" s.pef_admission = 'yes' AND s.nadra_pending = 'No'");
        }

        $query->groupBy('cl.id');
        //return $query->toSql();
        $attendance = $query->get();
        
        return $attendance;
    }

    public function student_attendance_total($student_id) {

        $student = Student::with('attendances')->findOrFail($student_id);
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Fetch attendance records for the current month
        $monthlyAttendance = $student->attendances->filter(function ($attendance) use ($startOfMonth, $endOfMonth) {
            return Carbon::parse($attendance->attendance_date)->between($startOfMonth, $endOfMonth);
        });
        // Count attendance types for the current month
        $presentCount = $monthlyAttendance->where('status', 'present')->count();
        $leaveCount = $monthlyAttendance->where('status', 'leave')->count();
        $absentCount = $monthlyAttendance->where('status', 'absent')->count();

        // Calculate the average present/total for the current month
        $totalDaysInMonth = $startOfMonth->daysInMonth;
        $averagePresent = $totalDaysInMonth > 0 ? ($presentCount / $totalDaysInMonth) : 0;

        // Determine today's attendance status
        $todayAttendance = $student->attendances->firstWhere('attendance_date', $today->toDateString());
        $todayStatus = $todayAttendance ? $todayAttendance->status : 'absent'; // Default to 'absent' if no record found

        // Determine yesterday's attendance status
        $yesterdayAttendance = $student->attendances->where('attendance_date', $yesterday->toDateString())->first();
        $yesterdayStatus = $yesterdayAttendance ? $yesterdayAttendance->status : 'absent'; // Default to 'absent' if no record found

        // Overall attendance data
        $total = $student->attendances->count();
        $totalPresent = $student->attendances->where('status', 'present')->count();
        $totalAbsent = $student->attendances->where('status', 'absent')->count();
        $totalLeave = $student->attendances->where('status', 'leave')->count();
        $percentPresent = $total > 0 ? ($totalPresent / $total) * 100 : 0;

        $attendanceData = [
            'student_id' => $student->id,
            'student_name' => $student->name,
            'total' => $total,
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'total_leave' => $totalLeave,
            'percent_present' => round($percentPresent, 2),
            'average_present' => round($averagePresent, 2),
            'this_month_present' => $presentCount,
            'this_month_leave' => $leaveCount,
            'this_month_absent' => $absentCount,
            'today_status' => $todayStatus,
            'yesterday_status' => $yesterdayStatus,
        ];


        return $attendanceData;
    
    }

    public function absent_student_each_class($data) {
        $today = ($data['date']) ?? Carbon::today();

        $isHoliday = Holiday::whereDate('holiday_date', $today)->exists();
        if ($isHoliday) {
            return response()->json([
                'message' => 'Today is a holiday. No students are absent.'
            ]);
        }
        $absentStudents = Classes::with(['students' => function ($query) use ($today) {
            $query->whereHas('attendances', function ($query) use ($today) {
                $query->whereDate('attendance_date', $today)
                      ->where(function ($query) {
                          $query->where('status', 'absent')
                                ->orWhere('status', 'leave');
                      });
            });
        }, 'students.parents' => function ($query) {
            $query->select('id', 'name', 'phone');
        }])
        ->get();

        $result = $absentStudents->map(function ($class) use ($today) {
            return [
                'class_name' => $class->name,
                'absent_students' => $class->students->map(function ($student) use ($today) {
                    $attendance = $student->attendances->where('attendance_date', $today)->first();
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'parent_name' => $student->parents->name,
                        'phone' => $student->parents->phone,
                        'attendace_id' => $attendance->id,
                        'comment' => $attendance->comment,


                    ];
                })
            ];
        });

        return $result;
    }

    public function absent_comment($data) {
        $attendance = StudentAttendance::findOrFail($data['attendance_id']);
        $attendance->comment = $data['comment'];
        $attendance->save();

        return response()->json([
            'message' => 'Absent comment updated successfully.'
        ]);
    }

    public function teachers_monthly_attendance_report(array $request)
    {
        $start_month = Carbon::createFromFormat('Y-m-d', $request['month'])->firstOfMonth()->format('Y-m-d');
        $end_month = Carbon::createFromFormat('Y-m-d', $request['month'])->lastOfMonth()->format('Y-m-d');
        $attendance = DB::select("
            SELECT 
                c.date,
                t.id AS teacher_id,
                t.name AS teacher_name,
                ta.opening_time,
                ta.created_at,
                COALESCE(ta.status, CASE 
                    WHEN h.holiday_date IS NOT NULL THEN h.description
                    WHEN DAYOFWEEK(c.date) = 1 THEN 'Sun'
                    WHEN ta.id IS NOT NULL THEN 'P'
                    WHEN ta.id IS NULL AND c.date <= CURRENT_DATE THEN '-'
                    ELSE '-'
                END) AS attendance_status
            FROM 
                calendar c
            CROSS JOIN 
                teachers t
            LEFT JOIN 
                teacher_attendances ta ON c.date = ta.attendance_date AND t.id = ta.teacher_id
            LEFT JOIN 
                holidays h ON c.date = h.holiday_date
            WHERE
                c.date BETWEEN ? AND ? AND t.status = 'active'
            ORDER BY 
                t.name, c.date;
        ", [$start_month, $end_month]);

        //print_r($attendance);
        foreach($attendance as $attend) {
            $teachers[$attend->teacher_id]['name'] = $attend->teacher_name;
            $teachers[$attend->teacher_id]['attendances'][] = array('status' => $attend->attendance_status, 'time' => $attend->created_at, 'opening_time' => $attend->opening_time );
        }

        return $teachers;
    }

    public function get_student_attendace_comments($student_id) {
        $attendancesWithComments = StudentAttendance::where('student_id', $student_id)
        ->where('comment', '<>', '')
        ->get();

        return $attendancesWithComments;
    }

    public function get_attendance_summry($month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        
        // Most absent students by class
        $absentees = StudentAttendance::where('created_at', 'like', $month . '%')
            ->where('status', 'absent')
            ->select('class_id', 'student_id', DB::raw('count(*) as absent_count'))
            ->groupBy('class_id', 'student_id')
            ->with(['students:id,name,roll_no', 'classes:id,name'])
            ->orderBy('absent_count', 'desc')
            ->get()
            ->groupBy('class_id');

        // Most present students by class
        $punctual = StudentAttendance::where('created_at', 'like', $month . '%')
            ->where('status', 'present')
            ->select('class_id', 'student_id', DB::raw('count(*) as present_count'))
            ->groupBy('class_id', 'student_id')
            ->with(['students:id,name,roll_no', 'classes:id,name'])
            ->orderBy('present_count', 'desc')
            ->get()
            ->groupBy('class_id');

        // Top 3 most punctual students overall
        $topPunctual = StudentAttendance::where('created_at', 'like', $month . '%')
            ->where('status', 'present')
            ->select('student_id', DB::raw('count(*) as present_count'))
            ->groupBy('student_id')
            ->with(['students:id,name,roll_no,class_id', 'students.stdclasses:id,name'])
            ->orderBy('present_count', 'desc')
            ->limit(3)
            ->get();

        return [
            'absentees' => $absentees,
            'punctual' => $punctual,
            'top_punctual' => $topPunctual
        ];
    }
   
}