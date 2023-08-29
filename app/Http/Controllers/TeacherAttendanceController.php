<?php
namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Teacher;
use App\Models\User;
use App\Models\SmsQueue;
use App\Models\Settings;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Models\TeacherAttendance;
use App\Models\TeacherPay;
use Illuminate\Support\Facades\DB;


class TeacherAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $date = $request->date;
        $month = $request->month;
        $start_month = '';
        $end_month = '';

        $type = $request->type;

        if($type == 'getteachers'){
            $result = (DB::SELECT(" SELECT u.name,u.id,u.type,u.pay,
            (select COUNT(ta.id) from teacher_attendances ta
            WHERE u.id = ta.user_id AND status='present' 
            GROUP by u.id
            ) AS present,
            
            (select COUNT(ta.id) from teacher_attendances ta
            WHERE u.id = ta.user_id AND status='absent' 
            GROUP by u.id
            ) AS absent,
            
            (select COUNT(ta.id) from teacher_attendances ta
            WHERE u.id = ta.user_id AND status='leave'
            GROUP by u.id
            ) AS onleave,
            
            (select COUNT(ta.id) from teacher_attendances ta
            WHERE u.id = ta.user_id AND status='holiday'
            GROUP by u.id
            ) AS holiday
            
            FROM users u;"));
            return response()->json(new JsonResponse(['teachers' => $result]));
        }

        if($type == 'teachers_salarygenerated'){
            $school_id = 1;
            $settings = Settings::find($school_id);
            $start_month = Carbon::createFromFormat('Y-m-d', $month)->firstOfMonth()->format('Y-m-d');
            $end_month = Carbon::createFromFormat('Y-m-d', $month)->lastOfMonth()->format('Y-m-d');
            $result = DB::table('users as u')
                        ->join('teacher_pay as p', 'p.user_id', '=', 'u.id')
                        ->select('u.name','u.id','u.type','u.pay','p.estimated_pay','p.month')
                        ->selectRaw('(SELECT SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) AS absent FROM teacher_attendances ta WHERE u.id = ta.user_id and attendance_date BETWEEN "'.$start_month.'" and "'.$end_month.'" GROUP BY ta.user_id) AS absent')
                        ->selectRaw('(SELECT SUM(CASE WHEN status = "leave" THEN 1 ELSE 0 END) AS absent FROM teacher_attendances ta WHERE u.id = ta.user_id and attendance_date BETWEEN "'.$start_month.'" and "'.$end_month.'" GROUP BY ta.user_id) AS leaves')
                        ->selectRaw('(SELECT sum(case when status = "holiday" or status = "present" then 1 else 0 end) AS working FROM teacher_attendances ta WHERE u.id = ta.user_id and attendance_date BETWEEN "'.$start_month.'" and "'.$end_month.'" GROUP BY ta.user_id) AS working')
                        ->where('u.type','App\Models\Teacher')
                        ->whereBetween('p.month',[$start_month,$end_month])
                        ->get();
                        $ans = ($result->count() > 0) ? 'Yes' : 'No';
            return response()->json(new JsonResponse(['teacherwithsalary' => $result, 'has_generated' => $ans, 'setting' => $settings]));
        }
        if($month) {
            $start_month = Carbon::createFromFormat('Y-m-d', $month)->firstOfMonth()->format('Y-m-d');
            $end_month = Carbon::createFromFormat('Y-m-d', $month)->lastOfMonth()->format('Y-m-d');
            $result = Teacher::with(['attendance' => function($query) use($start_month, $end_month){
                return $query->whereBetween('attendance_date',array($start_month,$end_month));
            }])->get();
        } else {
            $result = TeacherAttendance::when($month, function($query) use($start_month, $end_month) {
                $query->whereBetween('attendance_date',array($start_month,$end_month));
            })
            ->when($date, function($query) use($date) {
                $query->where('attendance_date',$date);
            })
            ->orderBy('user_id')->get();
        }
        return response()->json(new JsonResponse(['attendace' => $result]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $date = $request->date;
        $teachers = $request->teachers;
        $type = $request->type;
        if($type == 'generatepay'){
            $teachers_salary = $request->resource;
            print_r($teachers_salary);
            $month = $request->month;
            $teacher_array = array();
            $start_month = Carbon::createFromFormat('Y-m-d', $month)->firstOfMonth()->format('Y-m-d');
            $end_month = Carbon::createFromFormat('Y-m-d', $month)->lastOfMonth()->format('Y-m-d');
            foreach($teachers_salary as $teacher_salary)
                $teacher_array[] = array('estimated_pay' => $teacher_salary['pay']/31 *$teacher_salary['present'] , 'month' => $month, 'user_id' => $teacher_salary['id'] );
                $result_teacher_array= TeacherPay::insert($teacher_array);
                return response()->json(new JsonResponse(['examsreult' => $result_teacher_array]));
        }
        else{
            TeacherAttendance::where('attendance_date', $date)->delete();
                foreach($teachers as $data){
                    $attendance[] = [
                        'user_id' => $data['id'],
                        'status' => $data['attendance'],
                        'attendance_date' =>  $date,
                    ];

                    if($data['attendance'] == 'Absent') {
                        $sms['user_id'] = $data['id'];
                        $sms['message'] = 'Dear teacher '.$data['name'].',  Your are absent today from school.';
                        $sms['phone'] = $this->format_phone($data['phone']);
                        SmsQueue::create($sms);
                    }
                }
                TeacherAttendance::insert($attendance);

                }
    }

    function format_phone($phone) {
        $phone = ltrim($phone, '0'); //remove 0 from start
        $phone = ((strpos( $phone, "92" ) === 0)) ? $phone : '92'.$phone;
        $find = array(' ', '-');
        $replace = array('','');
        $phone = str_replace($find, $replace, $phone);
        return $phone;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function check_salary_generated(Request $request)
    {
        $month = $request->month;
        $start_month = Carbon::createFromFormat('Y-m-d', $month)->firstOfMonth()->format('Y-m-d');
        $end_month = Carbon::createFromFormat('Y-m-d', $month)->lastOfMonth()->format('Y-m-d');
        $result = DB::table('users as u')
                        ->join('teacher_pay as p', 'p.user_id', '=', 'u.id')
                        ->select('u.name','u.id','u.type','u.pay','p.estimated_pay','p.month')
                        ->where('u.type','App\Models\Teacher')
                        ->whereBetween('p.month',[$start_month,$end_month])
                        ->get();
                        $ans = ($result->count() > 0) ? 'Yes' : 'No';
            return response()->json(new JsonResponse(['has_generated' => $ans]));
    }
    public function generate_pay(Request $request)
    {
        $res = 'No';
        $school_id = 1;
        $settings = Settings::find($school_id);
        $month = $request->month;
        $start_month = Carbon::createFromFormat('Y-m-d', $month)->firstOfMonth()->format('Y-m-d');
        $end_month = Carbon::createFromFormat('Y-m-d', $month)->lastOfMonth()->format('Y-m-d');
        $result = DB::table('users as u')
                        ->select('u.name','u.id','u.type','u.pay')
                        ->selectRaw('(SELECT SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) AS absent FROM teacher_attendances ta WHERE u.id = ta.user_id and attendance_date BETWEEN "'.$start_month.'" and "'.$end_month.'" GROUP BY ta.user_id) AS absent')
                        ->selectRaw('(SELECT SUM(CASE WHEN status = "leave" THEN 1 ELSE 0 END) AS absent FROM teacher_attendances ta WHERE u.id = ta.user_id and attendance_date BETWEEN "'.$start_month.'" and "'.$end_month.'" GROUP BY ta.user_id) AS leaves')
                        ->selectRaw('(SELECT sum(case when status = "holiday" or status = "present" then 1 else 0 end) AS working FROM teacher_attendances ta WHERE u.id = ta.user_id and attendance_date BETWEEN "'.$start_month.'" and "'.$end_month.'" GROUP BY ta.user_id) AS working')
                        ->where('u.type','App\Models\Teacher')
                        ->get();
        $delete = (DB::DELETE("DELETE FROM teacher_pay WHERE month BETWEEN '$start_month' AND '$end_month'"));
        foreach($result as $row){
            $perday_pay = $row->pay/30;
            $working_days = $row->working;
            $absent = $row->absent;
            $leaves = $row->leaves;
            $allowed_leaves = $settings->teacher_leaves_allowed;
            $total_working = $working_days+$absent+$leaves;
            $total_off = $absent+$leaves;
            $month_days = 30;
            if($total_working < 15){
                $month_days = $total_working;
                $allowed_leaves = ($allowed_leaves)? $allowed_leaves/2:$allowed_leaves;
            }
            $days_to_pay = ($total_off>$allowed_leaves)?$month_days+$allowed_leaves-$total_off:$month_days;
            $estimate_pay = $perday_pay*$days_to_pay;
            $teacher_array = array('estimated_pay' => $estimate_pay , 'month' => $month, 'user_id' => $row->id );
            if($row->working){
                $res = 'Yes';
                $result_teacher_array= TeacherPay::insert($teacher_array);
            }
        }
        return response()->json(new JsonResponse(['has_generated' => $res]));
    }
}
