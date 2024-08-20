<?php
namespace App\Services\Contracts;


interface AttendanceServiceInterface
{
    public function getMonthlyAttendance($student_id, $date);
    public function get_student_attendance(int $student_id, string $date);
    public function student_attendance_marked(array $data);
    public function student_monthly_attendance_report(array $data);
    public function student_daily_classwise(array $data);
    public function teachers_monthly_attendance_report(array $data);
    public function absent_student_each_class(array $data);
    public function absent_comment(array $data);
    public function student_attendance_total(int $student_id);
}