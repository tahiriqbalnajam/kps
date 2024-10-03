<?php
namespace App\Models;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model {

    
    protected $fillable = [
        'user_id','class_id','name', 'father_name','father_cnic', 'doj','education', 'experience', 'gender', 
        'pay', 'cnic', 'address', 'phone', 'profession','active'
    ];


    public function attendances() {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function calculatePay($month, $year, $allowedLeaves = 1, $salary)
    {
        $totalDaysInMonth = '30';
        $workingDays = '30';

        $attendedDays = $this->attendances()
                             ->whereMonth('attendance_date', $month)
                             ->whereYear('attendance_date', $year)
                             ->where('status', 'present')
                             ->count();

        $absentDays = $workingDays - $attendedDays;
        $payableDays = $workingDays - max(0, $absentDays - $allowedLeaves);
        
        // Calculate the daily salary
        $dailySalary = round($salary / $totalDaysInMonth);
        $previous_balance = 0;
        // Calculate the total pay
        if($attendedDays > 0)
            $totalPay = $dailySalary * $payableDays;
        else
            $totalPay = 0;

        return array('total_days_month' => $totalDaysInMonth, 
                     'working_days' => $workingDays,
                     'present_days' => $attendedDays,
                     'absent_days' => $absentDays,
                     'payable_days'=> $payableDays,
                     'daily_salary' => $dailySalary,
                     'total_pay' => $totalPay,
                     'previous_balance' => $previous_balance
                    );
    }

    public function getTestsWithAverageMarks() {
        
        return DB::table('tests as t')
            ->join('classes as c', 't.class_id', '=', 'c.id')
            ->join('subjects as s', 't.subject_id', '=', 's.id')
            ->join('test_results as tr', 't.id', '=', 'tr.test_id')
            ->select(
                'c.name as class_name',
                's.title as subject_title',
                't.title as test_title',
                't.total_marks as total_marks',
                DB::raw('ROUND(AVG(tr.score),2) as average_marks'),
                DB::raw('ROUND(AVG(tr.score)/t.total_marks * 100, 0) as percent')
            )
            ->where('t.teacher_id', $this->id) // Using $this->id to get the current teacher's ID
            ->where('tr.absent', 'no') // Using $this->id to get the current teacher's ID
            ->groupBy('c.id', 's.id', 't.id')
            ->orderBy('c.name')
            ->orderBy('s.title')
            ->orderBy('t.title')
            ->get();
    }

}
