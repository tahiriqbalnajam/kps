<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Teacher;
use App\Models\SmsQueue;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Models\TeacherAttendance;

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
        if($month) {
            $start_month = Carbon::createFromFormat('Y-m-d', $month)->firstOfMonth()->format('Y-m-d');
            $end_month = Carbon::createFromFormat('Y-m-d', $month)->lastOfMonth()->format('Y-m-d');
            $result = Teacher::with(['attendance' => function($query) use($start_month, $end_month){
                return $query->whereBetween('created_at',array($start_month,$end_month));
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
