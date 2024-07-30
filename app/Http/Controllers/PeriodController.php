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
                'id', 'title', 'class_id', 'title',
            ])
            ->paginate($limit)
            ->appends(request()->query());
        return response()->json(new JsonResponse(['periods' => $periods]));
    }

    public function store(Request $request){
       print_r($request->all());

    $period = Period::create([
        'title' => $request->title,
        'start' => $request->period_start,
        'end' =>  $request->period_end,
    ]);

    return $period;
    }
}
