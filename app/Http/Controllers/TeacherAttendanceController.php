<?php
namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Teacher;
use App\Models\SmsQueue;
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
            $start_month = Carbon::createFromFormat('Y-m-d', $month)->firstOfMonth()->format('Y-m-d');
            $end_month = Carbon::createFromFormat('Y-m-d', $month)->lastOfMonth()->format('Y-m-d');
            $result = (DB::SELECT(" SELECT u.name, u.id, u.type, u.pay,
            (SELECT tp.estimated_pay FROM teacher_pay tp
             WHERE u.id = tp.user_id AND month BETWEEN '$start_month' AND '$end_month'
             LIMIT 1) AS estimated_pay
                FROM users u"));
            return response()->json(new JsonResponse(['teacherwithsalary' => $result]));
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
            $month = $request->month;
            $teacher_array = array();
            foreach($teachers_salary as $teacher_salary)
                $teacher_array[] = array('estimated_pay' => $teacher_salary['pay']/31 *$teacher_salary['present'] , 'month' => $month, 'user_id' => $teacher_salary['id'] );
                $result_teacher_array= TeacherPay::insert($teacher_array);
                return response()->json(new JsonResponse(['examsreult' => $result_teacher_array]));
            //$exam = TeacherPay::create($request->all());
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
}
