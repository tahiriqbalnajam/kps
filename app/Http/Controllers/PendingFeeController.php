<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PendingFeeController extends Controller
{
    const ITEM_PER_PAGE = 30;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       // print_r($request->all()); die();
        $searchParams = $request->all();
        $keyword = $request->get('keyword');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        DB::enableQueryLog();

        $latestFee = DB::table('fee')
                   ->select('student_id', DB::raw('MAX(id) AS feeid, max(payment_to_date) as payment_to_date, max(created_at) as paidat'))
                   ->groupBy('student_id');

        $students = DB::table('students')
                ->select('students.*', 'classes.name as classname','parents.name as parent', 'parents.phone as phone', 'f.payment_to_date','f.paidat')
                ->join('classes', 'classes.id','=','students.class_id')
                ->join('parents', 'parents.id','=','students.parent_id')
                ->leftJoinSub($latestFee, 'f', function ($join) {
                    $join->on('students.id', '=', 'f.student_id');
                })
                ->when($keyword, function ($query) use ($keyword) {
                     return $query->where('students.name', 'like', '%' . $keyword . '%');
                 })

                ->whereRaw('f.payment_to_date < CURRENT_DATE() and students.status = "enable"')
                ->paginate($limit);

/*

        $students = DB::table('students')
        ->select('*')
        ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
        ->join(DB::raw('( SELECT MAX(id) AS feeid,student_id,max(payment_to_date) as payment_to_date FROM fee GROUP by student_id
          ) as f'), function ($join) {
              $join->on ( 'f.student_id', '=', 'students.id' );
          })
        ->whereRaw('f.payment_to_date < CURRENT_DATE()')
        //->toSql();
        ->paginate($limit); 
       //dd(DB::getQueryLog());
       */
        return response()->json(new JsonResponse(['fee' => $students]));
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
        //
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
