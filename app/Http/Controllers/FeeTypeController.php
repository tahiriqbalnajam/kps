<?php

namespace App\Http\Controllers;

use App\Models\FeeType;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;

class FeeTypeController extends Controller
{
    const ITEM_PER_PAGE = 30;
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
        //DB::enableQueryLog(); // Enable query log
        $feetypes = FeeType::
                    when($keyword, function($query) use($keyword) {
                        return $query->where('title','like', '%'.$keyword.'%');
                    })
                    ->paginate(15);  
        //dd(DB::getQueryLog()); // Show results of log
        return response()->json(new JsonResponse(['feetypes' => $feetypes]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $feetype = new FeeType();
        $feetype->title = $request->title;
        $feetype->amount = $request->amount;
        $feetype->save();
        return response()->json(new JsonResponse(['feetype' => $feetype]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(FeeType $feetype)
    {
        return response()->json(new JsonResponse(['feetype' => $feetype]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FeeType $feetype)
    {
        $feetype->title = $request->title;
        $feetype->amount = $request->amount;
        $feetype->save();
        return response()->json(new JsonResponse(['feetype' => $feetype]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FeeType $feetype)
    {
        $feetype->delete();
        return response()->json(new JsonResponse('deleted successfully'));
    }
}
