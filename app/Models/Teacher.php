<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

}
