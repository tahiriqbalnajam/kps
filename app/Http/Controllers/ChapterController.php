<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ChapterController extends Controller
{
    const ITEM_PER_PAGE = 1000;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $chapters =  QueryBuilder::for(Chapter::class)
            ->allowedIncludes('subject', 'class')
            ->allowedFilters([
                'id', 'subject_id', 'class_id', 'title',
            ])
            ->paginate($limit)
            ->appends(request()->query());
        return response()->json(new JsonResponse(['chapters' => $chapters]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'class_id' => 'required',
            'subject_id' => 'required',
            'title' => 'required',
        ]);

        $chapter = Chapter::create([
            'class_id' => $validatedData['class_id'],
            'subject_id' => $validatedData['subject_id'],
            'title' => $validatedData['title'],
        ]);

        return $chapter;
    }

    /**
     * Display the specified resource.
     */
    public function show(Chapter $chapter)
    {
        if($chapter)
            return response()->json(new JsonResponse(['chapter' => $chapter]));
        else
            return response()->json(new JsonResponse(['error' => 'Sorry chapter not found'], 404));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
