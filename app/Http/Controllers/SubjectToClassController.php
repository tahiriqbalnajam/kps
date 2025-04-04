<?php

namespace App\Http\Controllers;

use App\Models\SubjectToClass;
use App\Models\Classes;
use App\Models\ClassSubject;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class SubjectToClassController extends Controller
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
        $subjects =  QueryBuilder::for(Classes::class)->with('subjects')
        ->allowedFilters([
            'id','title'
        ])
        ->paginate($limit)
        ->appends(request()->query());
        //DB::enableQueryLog(); // Enable query log
        // $subjects = Classes::with('subjects')
        // ->when($keyword, function ($query) use ($keyword) {
        //         $query->where('title', 'like', '%'.$keyword.'%');
        // })
        // ->when($stdclass, function ($query) use ($stdclass) {
        //         $query->where('id', $stdclass);
        // })
        // ->paginate($limit);
        //dd(DB::getQueryLog()); // Show results of log
        return response()->json(new JsonResponse(['classubj' => $subjects]));
    }

    /**
     * Get subjects against a class.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSubjectsByClass(Request $request)
    {
        $classId = $request->get('class_id');
        $subjects = QueryBuilder::for(ClassSubject::class)
            ->allowedFilters([AllowedFilter::exact('class_id')])
            ->where('class_id', $classId)
            ->with('subject')
            //->get();
            ->paginate($request->get('limit', 15));
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
        $subjects = $request->subject_ids;
        $class_id = $request->class_id;
        $data = array();
        foreach($subjects as $subject)
            $data[] = array('class_id' => $class_id, 'subject_id' => $subject );
        
        SubjectToClass::insert($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stclass = Classes::find($id);
        $subject = $stclass->subjects;
        return response()->json(new JsonResponse(['classubj' => $stclass]));
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
        $stclass = Classes::find($id);	
        $subject_ids = $request->subject_ids;
        $stclass->subjects()->sync($subject_ids);
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
}
