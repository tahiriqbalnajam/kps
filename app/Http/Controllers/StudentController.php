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

    public function addattendance(Request $request){
        $this->studentService->addAttendance($request->all());
    }

    public function edit_class(Request $request){
        $selected_students = $request->multiStudent;
        $desired_class = $request->changeClass;
        $this->studentService->editClass($selected_students, $desired_class);

    }
}
