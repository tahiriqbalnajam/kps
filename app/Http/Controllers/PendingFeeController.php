<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

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
        // DB::enableQueryLog(); // Optional: Keep for debugging if needed

        // Subquery to get the latest fee payment date for each student
        $latestFee = DB::table('fee')
                   ->select('student_id', DB::raw('MAX(payment_to_date) as payment_to_date')) // Get only the latest payment_to_date
                   ->groupBy('student_id');

        $students = DB::table('students')
                ->select('students.*', 'classes.name as classname','parents.name as parent', 'parents.phone as phone', 'f.payment_to_date') // Select payment_to_date from subquery 'f'
                ->join('classes', 'classes.id','=','students.class_id')
                ->join('parents', 'parents.id','=','students.parent_id')
                ->leftJoinSub($latestFee, 'f', function ($join) {
                    $join->on('students.id', '=', 'f.student_id');
                })
                ->where('students.status', '=', 'enable') // Filter only enabled students
                ->when($keyword, function ($query) use ($keyword) {
                     // Apply keyword search on student name, parent name, or phone
                     return $query->where(function (Builder $q) use ($keyword) {
                         $q->where('students.name', 'like', '%' . $keyword . '%')
                           ->orWhere('parents.name', 'like', '%' . $keyword . '%')
                           ->orWhere('parents.phone', 'like', '%' . $keyword . '%');
                     });
                 })
                 // Filter for pending fees:
                 // - Students whose last payment date is before the 1st of the current month
                 // - Or students who have never paid (payment_to_date is NULL)
                ->where(function (Builder $query) {
                    $query->whereRaw('f.payment_to_date < DATE_FORMAT(CURRENT_DATE(), "%Y-%m-01")')
                          ->orWhereNull('f.payment_to_date');
                })
                ->orderBy('classes.name') // Optional: Order by class name
                ->orderBy('students.name') // Optional: Then by student name
                ->paginate($limit);

        // dd(DB::getQueryLog()); // Optional: Keep for debugging if needed

        // Return the paginated results
        return response()->json(new JsonResponse(['students' => $students])); // Changed key to 'students' for clarity
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
