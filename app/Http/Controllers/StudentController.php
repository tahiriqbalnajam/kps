<?php

namespace App\Http\Controllers;

use Response;
use App\Models\User;
use App\Models\Classes;
use App\Models\Parents;
use App\Models\Student;
use Illuminate\Support\Arr;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Models\StudentAttendance;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\Contracts\StudentServiceInterface;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;

class StudentController extends Controller
{
    protected $studentService;
    const ITEM_PER_PAGE = 1000;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(StudentServiceInterface $studentService)
    {
        $this->studentService = $studentService;
    }


    public function index(Request $request)
    {
        $searchParams = $request->all();
        
        // Check if filter[stdclass] exists and convert to filter[section_id]
        // if (isset($searchParams['filter']['stdclass'])) {
        //     $stdClassValue = $searchParams['filter']['stdclass'];
            
        //     // Extract numeric value from class_X or section_X format
        //     if (preg_match('/class_(\d+)/', $stdClassValue, $matches)) {
        //         $classId = $matches[1];
        //         $searchParams['filter']['stdclass'] = $classId;
        //     }
        //     if(preg_match('/section_(\d+)/', $stdClassValue, $matches)) {
        //         $sectionId = $matches[1];
        //         unset($searchParams['filter']['stdclass']);
        //         $searchParams['filter']['section_id'] = $sectionId;
        //     }
        // }
        
        $students = $this->studentService->listStudents($searchParams);
        return response()->json(new JsonResponse(['students' => $students]));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $student = $this->studentService->storeStudent($request->all());
        return response()->json(new JsonResponse(['student' => $student]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Student $student)
    {
        $student = Student::with('parents', 'stdclasses')->find($student->id);
        return response()->json(new JsonResponse(['student' => $student]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        $input = $request->all();
        $this->studentService->updateStudent($student, $input);
        return response()->json(new JsonResponse(['student' => $student]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function exportStudents(Request $request)
    {
        $searchParams = $request->all();
        $studentQuery = $this->studentService->listStudents($searchParams);

        return Excel::download(new StudentsExport($studentQuery), 'students_export.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function addattendance(Request $request){
        $this->studentService->addAttendance($request->all());
    }

    public function getSubjectWiseScores($studentId) {
        $results = $this->studentService->getSubjectWiseScores($studentId);
        return response()->json(new JsonResponse(['results' => $results]));
    }

    public function edit_class(Request $request){
        $selected_students = $request->multiStudent;
        $desired_class = $request->changeClass;
        $this->studentService->editClass($selected_students, $desired_class);

    }

    public function online_attendance($id) {
        $student = Student::find($id);
        if (!$student) {
            return response()->json(new JsonResponse(['message' => 'Student not found'], 404));
        }

        // Check if attendance already exists for today
        $todayAttendance = StudentAttendance::where('student_id', $id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if (!$todayAttendance) {
            // Create new attendance record
            StudentAttendance::create([
                'student_id' => $id,
                'status' => 'present',
                'created_at' => now(),
            ]);
            return response()->json(new JsonResponse(['message' => 'Attendance marked successfully']));
        }

        return response()->json(new JsonResponse(['message' => 'Attendance already submitted']));
    }
}
