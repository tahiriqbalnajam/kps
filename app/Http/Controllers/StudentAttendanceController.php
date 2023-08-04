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
                                        ->whereBetween('created_at',array($start_month,$end_month))
                                        ->orderBy('student_id')
                                        ->get();

        $students = Student::with(['attend' => function($query) use($class_id, $start_month, $end_month){
            return $query->where('class_id', $class_id)->whereBetween('created_at',array($start_month,$end_month));
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
                'created_at' =>  $date,
            ];

            if($data['attendance'] == 'Absent') {
                $sms['student_id'] = $data['id'];
                $sms['message'] = 'Dear, '.$data['parents']['name'].',  Your child \''.$data['name'].'\' is absent today';
                $sms['phone'] = $this->format_phone($data['parents']['phone']);
                SmsQueue::create($sms);
            }
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
        $attendance =  DB::select("SELECT 
        c.name, 
        count(st.id) as absent, 
        (select count(id) from `student_attendances` where class_id = st.class_id and created_at = date('$date')) as total  FROM `student_attendances` st
        LEFT JOIN classes c on c.id = st.class_id
        where (st.status = 'absent' || st.status = 'leave') and  created_at = date('$date')
        GROUP by st.class_id, c.name");

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
