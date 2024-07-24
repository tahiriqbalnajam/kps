<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use App\Actions\RandomSort;

class QuestionController extends Controller
{
    const ITEM_PER_PAGE = 10;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $questions = QueryBuilder::for(Question::class)
            ->with(['chapter'])
            ->allowedFilters([
                'id','chapter_id', 'question_text', 'choice_1', 'choice_2', 'choice_3', 'choice_4'
            ])
            ->allowedSorts([
                AllowedSort::custom('random', new RandomSort(), 'name'),
            ])
            ->paginate($limit)
            ->appends(request()->query());
        return response()->json(new JsonResponse(['questions' => $questions]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'questions' => [
                'required',
                'array',
                'min:1',
            ],
            'questions.*.question_text' => 'required|string',
            'questions.*.choice_1' => 'required|string',
            'questions.*.choice_2' => 'required|string',
            'questions.*.choice_3' => 'required|string',
            'questions.*.choice_4' => 'required|string',
            'questions.*.correct_choice' => 'required|string|in:choice_1,choice_2,choice_3,choice_4',
        ]);

        $questionsData = $request->input('questions');
        $questions = [];

        foreach ($questionsData as $questionData) {
            $questionData['chapter_id'] = $request->input('chapter_id');
            $question = Question::create($questionData);
            $questions[] = $question;
        }

        return response()->json($questions, 201);

        return response()->json($question, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json(null, 204);

    }
}
