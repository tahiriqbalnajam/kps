<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Laravue\JsonResponse;
use App\Models\Period;
use Spatie\QueryBuilder\QueryBuilder;

class PeriodController extends Controller
{
    const ITEM_PER_PAGE = 1000;
    public function index(Request $request) {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $periods =  QueryBuilder::for(Period::class)
            ->allowedIncludes('subject', 'class')
            ->allowedFilters([
                'id', 'title', 'start', 'end',

            ])
            ->paginate($limit)
            ->appends(request()->query());
        return response()->json(new JsonResponse(['periods' => $periods]));
    }

    public function store(Request $request){
        $request->validate([
            'title' => 'required',
            'start' => 'required',
            'end' => 'required',
        ]); 

        $period = Period::create([
            'title' => $request->title,
            'start' => $request->start,
            'end' =>  $request->end,
        ]);

        return response()->json(new JsonResponse(['period' => $period]));
    }

    public function show($id) {
        $period = Period::find($id);
        return response()->json(new JsonResponse(['period' => $period]));
    }

    public function update(Request $request, $id) {
        $period = Period::find($id);
        $period->update($request->all());
        return response()->json(new JsonResponse(['period' => $period]));
    }

    public function destroy($id) {
        Period::destroy($id);
        return response()->json(new JsonResponse(['msg' => 'Deleted successfully.']));
    }



}
