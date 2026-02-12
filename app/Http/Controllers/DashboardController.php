<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Dashboard;
use App\Models\StudentAttendance; 
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;


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
        $student_birthday = $this->getTodaysBirthdayStudents();
        $teacher_birthday = $this->getTodaysBirthdayTeachers();
        $at_risk_students = $this->getAtRiskStudents();

        $newAdmissions = Student::whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->where('status', 'enable')
            ->count();

        $newAdmissionsPerClass = Student::with('stdclasses')
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->where('status', 'enable')
            ->get()
            ->groupBy('class')
            ->map(function ($students) {
            return [
                'count' => $students->count(),
                'class_name' => $students->first()->stdclasses->name ?? 'Unassigned'
            ];
            });
        return response()->json(new JsonResponse(['total_students' => $total_students, 
                                                   'total_absent_students' => $total_absent_students,
                                                   'total_teachers' => $total_teachers,
                                                   'total_absent_teachers' => $absent_teachers['total_absent_teachers'],
                                                   'absent_teachers' => $absent_teachers['absent_teachers'],
                                                   'student_birthdays' => $student_birthday,
                                                   'teacher_birthdays' => $teacher_birthday,
                                                   'newAdmissions' => $newAdmissions,
                                                   'newAdmissionsPerClass' => $newAdmissionsPerClass,
                                                   'at_risk_students' => $at_risk_students
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
        $absentStudents = Student::where('status', 'enable')
            ->whereDoesntHave('attendances', function ($query) use ($date) {
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
        $absentTeachers = Teacher::where('status','active')->whereDoesntHave('attendances', function ($query) use ($date) {
            $query->whereDate('attendance_date', $date)->where('status', 'present');
        })
        // ->whereHas('attendances', function ($query) use ($date) {
        //     $query->whereDate('attendance_date', $date)->where('status', 'absent')->orWhere('status', 'leave');
        // })
        ->pluck('name');

        // Count the total number of absent teachers
        $totalAbsent = $absentTeachers->count();

        return ['absent_teachers' => $absentTeachers,'total_absent_teachers' => $totalAbsent];
    }


    public function getTodaysBirthdayStudents()
    {
        $today = Carbon::now()->format('m-d');
        $students = Student::with('stdclasses')
            ->whereRaw('DATE_FORMAT(dob, "%m-%d") = ?', [$today])
            ->get()
            ->map(function ($student) {
            return [
                'name' => $student->name,
                'dob' => $student->dob,
                'class' => $student->stdclasses->name ?? 'No Class'
            ];
            });
        return $students;
    }
    
    public function getTodaysBirthdayTeachers()
    {
        $today = Carbon::now()->format('m-d');
        $teachers = Teacher::where('status', 'active')
            ->whereRaw('DATE_FORMAT(dob, "%m-%d") = ?', [$today])
            ->get()
            ->map(function ($teacher) {
            return [
                'name' => $teacher->name,
                'dob' => $teacher->dob,
                'subject' => $teacher->subject ?? 'N/A'
            ];
            });
        return $teachers;
    }

    public function getAtRiskStudents()
    {
        $atRisk = collect();

        // 1. Consecutive Absences (Last 3 recorded days)
        // Get the last 3 distinct dates where attendance was taken
        $lastDates = StudentAttendance::select('attendance_date')
            ->distinct()
            ->orderBy('attendance_date', 'desc')
            ->limit(3)
            ->pluck('attendance_date');

        if ($lastDates->count() >= 3) {
            $consecutiveAbsents = StudentAttendance::whereIn('attendance_date', $lastDates)
                ->where('status', 'absent')
                ->select('student_id', DB::raw('count(*) as count'))
                ->groupBy('student_id')
                ->having('count', '>=', 3)
                ->with(['students' => function($q) {
                    $q->select('id', 'name', 'class_id', 'parent_id')->with('stdclasses', 'parents');
                }])
                ->get();

            foreach ($consecutiveAbsents as $record) {
                if ($record->students) {
                    $atRisk->push([
                        'student_id' => $record->student_id,
                        'name' => $record->students->name,
                        'class' => $record->students->stdclasses->name ?? 'N/A',
                        'parent_phone' => $record->students->parents->phone ?? 'N/A',
                        'reason' => '3 Consecutive Absences',
                        'type' => 'danger' // UI purpose
                    ]);
                }
            }
        }

        // 2. Low Monthly Attendance (< 75%)
        $startOfMonth = Carbon::now()->startOfMonth();
        $lowAttendance = StudentAttendance::where('attendance_date', '>=', $startOfMonth)
            ->select('student_id', 
                DB::raw('count(*) as total_days'),
                DB::raw("sum(case when status = 'present' then 1 else 0 end) as present_days"))
            ->groupBy('student_id')
            ->havingRaw('(sum(case when status = "present" then 1 else 0 end) / count(*)) * 100 < 75')
            ->with(['students' => function($q) {
                $q->select('id', 'name', 'class_id', 'parent_id')->with('stdclasses', 'parents');
            }])
            ->get();

        foreach ($lowAttendance as $record) {
            // Avoid duplicates if already added for consecutive absence
            if (!$atRisk->contains('student_id', $record->student_id) && $record->students) {
                 $percentage = $record->total_days > 0 ? round(($record->present_days / $record->total_days) * 100) : 0;
                 $atRisk->push([
                    'student_id' => $record->student_id,
                    'name' => $record->students->name,
                    'class' => $record->students->stdclasses->name ?? 'N/A',
                    'parent_phone' => $record->students->parents->phone ?? 'N/A',
                    'reason' => "Low Attendance ({$percentage}%)",
                    'type' => 'warning'
                ]);
            }
        }

        return $atRisk->values();
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
