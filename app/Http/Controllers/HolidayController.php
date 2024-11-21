<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Collection;

class HolidayController extends Controller
{
    const ITEM_PER_PAGE = 15;
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);

        $holidays =  QueryBuilder::for(Holiday::class)
            ->allowedFilters([
                'description',
                'holiday_date',
            ])
            ->paginate($limit)
            ->appends(request()->query());
        return response()->json(new JsonResponse(['holidays' => $holidays]));
        
    }

    public function store(Request $request)
    {

        $holiday = Holiday::create($request->all());
        return response()->json($holiday, 201);
    }

    public function show($id)
    {
        $holiday = Holiday::find($id);
        if (!$holiday) {
            return response()->json(['message' => 'Holiday not found'], 404);
        }
        return response()->json($holiday);
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::find($id);
        if (!$holiday) {
            return response()->json(['message' => 'Holiday not found'], 404);
        }
        $holiday->update($request->all());
        return response()->json($holiday);
    }

    public function destroy($id)
    {
        $holiday = Holiday::find($id);
        if (!$holiday) {
            return response()->json(['message' => 'Holiday not found'], 404);
        }
        $holiday->delete();
        return response()->json(['message' => 'Holiday deleted']);
    }
}
