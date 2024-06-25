<?php
namespace App\Services;

use DB;
use Carbon\Carbon;
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
    //     $attendance = DB::select("
    //     SELECT
    //         cl.name AS class_title,
    //         COUNT(DISTINCT s.id) AS total_students,
    //         COUNT(DISTINCT CASE WHEN s.gender = 'male' THEN s.id END) AS total_male,
    //         COUNT(DISTINCT CASE WHEN s.gender = 'female' THEN s.id END) AS total_female,
    //         COUNT(CASE WHEN s.gender = 'male' AND sa.status = 'present' THEN 1 END) AS total_male_present,
    //         COUNT(CASE WHEN s.gender = 'male' AND (sa.status = 'absent' OR sa.status = 'leave') THEN 1 END) AS total_male_absent,
    //         COUNT(CASE WHEN s.gender = 'female' AND sa.status = 'present' THEN 1 END) AS total_female_present,
    //         COUNT(CASE WHEN s.gender = 'female' AND (sa.status = 'absent' OR sa.status = 'leave') THEN 1 END) AS total_female_absent,
    //         COUNT(CASE WHEN sa.status = 'present' THEN 1 END) AS total_present,
    //         COUNT(CASE WHEN (sa.status = 'absent'  OR sa.status = 'leave') THEN 1 END) AS total_absent,
    //         (COUNT(CASE WHEN sa.status = 'present' THEN 1 END) / COUNT(sa.id)) * 100 AS present_percentage,
    //         (COUNT(CASE WHEN (sa.status = 'absent' OR sa.status = 'leave') THEN 1 END) / COUNT(sa.id)) * 100 AS absent_percentage
    //     FROM
    //         students s
    //     JOIN
    //         student_attendances sa ON s.id = sa.student_id AND s.class_id = sa.class_id
    //     JOIN
    //         classes cl ON s.class_id = cl.id
    //     LEFT JOIN
    //         calendar c ON sa.attendance_date = c.date
    //     LEFT JOIN
    //         holidays h ON sa.attendance_date = h.holiday_date
    //     WHERE
    //         h.holiday_date IS NULL
    //         AND c.date = ?
    //         AND ?
    //     GROUP BY
    //         cl.id;
    //   ", [$date,  $pef_filter]);
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
                (COUNT(CASE WHEN sa.status = "present" THEN 1 END) / COUNT(sa.id)) * 100 AS present_percentage,
                (COUNT(CASE WHEN (sa.status = "absent" OR sa.status = "leave") THEN 1 END) / COUNT(sa.id)) * 100 AS absent_percentage
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

        $attendance = $query->get();
        
        return $attendance;
    }
}