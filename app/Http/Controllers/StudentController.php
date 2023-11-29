<?php

namespace App\Http\Controllers;

use Response;
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

class StudentController extends Controller
{
    const ITEM_PER_PAGE = 1000;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);        
        $students = QueryBuilder::for(Student::class)
                    ->with('parents','stdclasses','class_session')
                    ->allowedFilters(['id','name', 'roll_no', 'adminssion_number', 
                                        AllowedFilter::partial('parent_phone', 'parents.phone'),
                                        AllowedFilter::partial('parent_name', 'parents.name'),
                                        AllowedFilter::exact('stdclass', 'stdclasses.id')
                                    ])
                    ->paginate($limit);
        return response()->json(new JsonResponse(['students' => $students]));
    }


    // public function index(Request $request)
    // {
    //     $searchParams = $request->all();
    //     $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
    //     $keyword = $request->get('keyword');
    //     $stdid = $request->get('id');
    //     $stdclass = $request->get('stdclass');
    //     $filtercol = $request->get('filtercol');
        
    //     $all = ($request->get('filtercol') == 'all') ? true : false;
    //     //DB::enableQueryLog(); // Enable query log
    //     $students = Student::with('parents','stdclasses','class_session')
    //     ->when($all || ($filtercol == 'parent_name' && !empty($keyword)), function ($query) use ($keyword) {
    //         $query->whereHas('parents', function($query) use($keyword) {
    //             $query->where('parents.name', 'like', '%'.$keyword.'%');
    //         });
    //     })
    //     ->when($all || ($filtercol == 'name' && !empty($keyword)), function ($query) use ($all, $keyword) {
    //         if($all)
    //             return $query->orWhere('name', 'like', '%' . $keyword . '%');
    //         else
    //             return $query->where('name', 'like', '%' . $keyword . '%');
    //     })
    //     ->when($all || ($filtercol == 'roll_no' && !empty($keyword)), function ($query) use ($all, $keyword) {
    //         if($all)
    //             return $query->orWhere('roll_no', $keyword);
    //         else
    //             return $query->where('roll_no', $keyword);
    //     })
    //     ->when($all || ($filtercol == 'parent_phone' && !empty($keyword)), function ($query) use ($all, $keyword) {
    //         $query->whereHas('parents', function($query) use($keyword) {
    //             $query->where('parents.phone', 'like', '%'.$keyword.'%');
    //         });
    //     })
    //     ->when($stdid, function ($query) use ($stdid) {
    //         return $query->where('id', $stdid);
    //     })
    //     ->when($stdclass, function ($query) use ($stdclass) {
    //         return $query->where('class_id', $stdclass);
    //     })
    //     ->paginate($limit);
    //     //dd(DB::getQueryLog()); // Show results of log
    //     return response()->json(new JsonResponse(['students' => $students]));
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Student::create($request->all());
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
        $student->fill($input)->save();
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
        $date = $request->date;
        $students = $request->students;
        $id = $students[0]->id;
        foreach($students as $data){
            $attendance[] = [
                'class_id' => $data['class_id'],
                'student_id' => $data['id'],
                'status' => $data['attendance'],
                'created_at' =>  $date,
            ];
        }
        StudentAttendance::insert($attendance);
    }
    public function edit_class(Request $request){
        $selected_students = $request->multiStudent;
        $desired_class = $request->changeClass;
        foreach($selected_students as $students){
            Student::where('id', '=', $students)
            ->update([
                'class_id' => $desired_class // Add as many as you need
            ]);
        }

    }
}
