<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;

class SubjectController extends Controller
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
        $keyword = $request->get('keyword');;
        //DB::enableQueryLog(); // Enable query log
        $subjects = Subject::where('title', 'like', '%'.$keyword.'%')
        // ->when($keyword, function ($query) use ($keyword) {
        //         $query->where('title', 'like', '%'.$keyword.'%');
        // })
        ->paginate($limit);
        //dd(DB::getQueryLog()); // Show results of log
        return response()->json(new JsonResponse(['subjects' => $subjects]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Subject::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Subject $subject)
    {
        return response()->json(new JsonResponse(['subject' => $subject]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Subject $subject)
    {
        $input = $request->all();
        $subject->fill($input)->save();
        return response()->json(new JsonResponse(['subject' => $subject]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Subject::destroy($id);
        return response()->json(new JsonResponse(['msg' => 'Deleted successfully.']));
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
}
