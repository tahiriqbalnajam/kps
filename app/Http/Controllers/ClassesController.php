<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
class ClassesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keyword = $request->keyword;
        $classes = Classes::withCount(['students', 
                    'students as males_count' => function ($query) {
                        $query->where('gender', 'male');
                    },
                    'students as females_count' => function ($query) {
                        $query->where('gender', 'female');
                    }])
                    ->when($keyword, function ($query) use ($keyword) {
                        return $query->where('classes.name', 'like', '%' . $keyword . '%');
                    })
                    //->withCount('getMaleCountAttribute','getFemaleCountAttribute')
                    ->select('classes.id','classes.name',
                                DB::raw('COUNT(students.class_id) AS total_students'))
                    ->leftJoin('students','students.class_id','=','classes.id')
                    ->groupBy('students.class_id')
                    ->groupBy('classes.id')
                    ->groupBy('classes.name')
                    ->paginate(30);
                   // dd($classes);
        return response()->json(new JsonResponse(['classes' => $classes]));
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
        $class = new Classes();
        $class->name = $request->name;
        $class->save();
        return response()->json(new JsonResponse(['class' => $class]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Classes $class)
    {

        return response()->json(new JsonResponse(['class' => $class]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $class = Classes::find($id);
        $class->name = $request->name;
        $class->save();
        return response()->json(new JsonResponse(['class' => $class]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Classes $class)
    {
        $class->delete();
        return response()->json(new JsonResponse('Deleted successfully'));
    }
}
