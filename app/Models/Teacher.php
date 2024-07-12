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

    public function calculatePay($month, $year, $allowedLeaves = 2, $salary)
    {
        $totalDaysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $workingDays = Calendar::whereMonth('date', $month)
                               ->whereYear('date', $year)
                               ->whereNotIn('date', Holiday::pluck('holiday_date'))
                               ->count();

        $attendedDays = $this->attendances()
                             ->whereMonth('attendance_date', $month)
                             ->whereYear('attendance_date', $year)
                             ->where('status', 'present')
                             ->count();

        $absentDays = $workingDays - $attendedDays;
        $payableDays = $workingDays - max(0, $absentDays - $allowedLeaves);
        
        // Calculate the daily salary
        $dailySalary = $salary / $totalDaysInMonth;
        
        // Calculate the total pay
        $totalPay = $dailySalary * $payableDays;

        return array('total_days_month' => $totalDaysInMonth, 
                     'working_days' => $workingDays,
                     'attende_days' => $attendedDays,
                     'absent_days' => $absentDays,
                     'payable_days'=> $payableDays,
                     'daily_salary' => $dailySalary,
                     'total_pay' => $totalPay);
    }

}
