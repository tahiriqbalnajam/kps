<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Dashboard;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;


class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $date = Carbon::now();
        $total_students = Student::where('status', 'enable')->count();
        $total_absent_students = $this->getTotalAbsentStudents($date);
        $total_teachers = Teacher::where('status', 'active')->count();
        $absent_teachers = $this->getTotalAbsentTeachers($date);
        return response()->json(new JsonResponse(['total_students' => $total_students, 
                                                   'total_absent_students' => $total_absent_students,
                                                   'total_teachers' => $total_teachers,
                                                   'total_absent_teachers' => $absent_teachers['total_absent_teachers'],
                                                   'absent_teachers' => $absent_teachers['absent_teachers']
                                                ]));


    }

    public function getTotalAbsentStudents($date)
    {
        $date = Carbon::parse($date);

        $isHoliday = Holiday::whereDate('holiday_date', $date)->exists();

        if ($isHoliday) {
            return 0; 
        }

        $holidayDates = Holiday::whereDate('holiday_date', $date)->pluck('holiday_date')->toArray();
        $absentStudents = Student::whereDoesntHave('attendances', function ($query) use ($date) {
            $query->whereDate('attendance_date', $date)->where('status', 'present');
        })->whereDoesntHave('attendances', function ($query) use ($holidayDates) {
            $query->whereIn('attendance_date', $holidayDates);
        })->get();

        $totalAbsent = $absentStudents->count();

        return $totalAbsent;
    }

    public function getTotalAbsentTeachers($date)
    {
        $date = Carbon::parse($date);

        $isHoliday = Holiday::whereDate('holiday_date', $date)->exists();

        if ($isHoliday) {
            return 0; 
        }

        // Fetch teachers who have attendance marked as 'absent' or no attendance marked at all
        $absentTeachers = Teacher::whereDoesntHave('attendances', function ($query) use ($date) {
            $query->whereDate('attendance_date', $date)->where('status', 'present');
        })->whereHas('attendances', function ($query) use ($date) {
            $query->whereDate('attendance_date', $date)->where('status', 'absent')->orWhere('status', 'leave');
        })->pluck('name');

        // Count the total number of absent teachers
        $totalAbsent = $absentTeachers->count();

        return ['absent_teachers' => $absentTeachers,'total_absent_teachers' => $totalAbsent];
    }

    /**
     * Show the form for creating a new resource.
     *
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Dashboard  $dashboard
     * @return \Illuminate\Http\Response
     */
    public function show(Dashboard $dashboard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Dashboard  $dashboard
     * @return \Illuminate\Http\Response
     */
    public function edit(Dashboard $dashboard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Dashboard  $dashboard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Dashboard $dashboard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Dashboard  $dashboard
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dashboard $dashboard)
    {
        //
    }
}
