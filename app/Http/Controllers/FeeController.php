<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\FeeMeta;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    const ITEM_PER_PAGE = 3000;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = $request->get('keyword');
        $stdclass = $request->get('stdclass');
        $feetype_id = $request->get('feetype_id');
        //$stdclass = '11';
        $student_id = $request->get('id');
        $date = $request->get('date');
        $pending = $request->get('pending');

        // Get the fee type title
        $feeType = DB::table('fee_types')->where('id', $feetype_id)->value('title');
        // Build the base query that will be reused
        $baseQuery = Fee::with(['feetype', 'student.stdclasses'])
            ->with(['fee_meta' => function($query) use ($feeType) {
                if ($feeType) {
                $query->where('meta_key', $feeType);
                }
            }])
            ->when($student_id,  function($q) use ($student_id) {
                return $q->where('student_id', $student_id);
            })
            ->when($keyword,  function($q) use ($keyword) {
                return $q->where('student.name', 'like', '%'.$keyword.'%');
            })
            ->when($feetype_id, function($q) use ($feeType) {
                return $q->whereHas('fee_meta', function($query) use ($feeType) {
                $query->where('meta_key', $feeType);
                });
            })
            ->whereHas('student.stdclasses', function ($query) use ($stdclass) {
                if ($stdclass) {
                $query->where('class_id', $stdclass);
                }
            })
            ->when($date, function($q) use($date) {
                $start_date = Carbon::parse($date[0])->startOfDay();
                $end_date = Carbon::parse($date[1])->endOfDay();
                return $q->whereBetween('created_at', [$start_date, $end_date]);
            });
        
        // Clone the base query for pagination
        $fee = (clone $baseQuery)
                    ->orderBy('created_at', 'desc')
                    ->paginate($limit);

        // Calculate total fee amount using the same filters but without pagination
        $totalFeeCollected = (clone $baseQuery)
                    ->sum('amount');

        return response()->json(new JsonResponse([
            'fee' => $fee, 
            'totalFeeCollected' => $totalFeeCollected
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
           
        try {
            DB::beginTransaction();

            $total = 0;
            foreach($request->fee_meta as $key => $value){
                $total += $value['meta_value'];
            }

            $fee = new Fee();
            $fee->student_id = $request->student_id;
            $fee->payment_from_date = Carbon::parse($request->feefromto[0])->startOfMonth()->toDateString();
            $fee->payment_to_date = Carbon::parse($request->feefromto[1])->endOfMonth()->toDateString();
            $fee->amount = $total;
            $fee->fee_type_id = $request->fee_type_id;
            $fee->save();

            foreach($request->fee_meta as $key => $value){
                $fee_meta = new FeeMeta();
                $fee_meta->meta_key = $value['meta_key'];
                $fee_meta->meta_value = $value['meta_value'];
                $fee_meta->fee_id = $fee->id;
                $fee_meta->save();
            }

            DB::commit();

            return response()->json(new JsonResponse(['fee' => $fee]));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(new JsonResponse(['error' => $e->getMessage()]), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $fee = Fee::where('id',$id)->with(['feetype','student.parents','student.stdclasses', 'fee_meta'])->first();
        return response()->json(new JsonResponse(['fee' => $fee]));
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
