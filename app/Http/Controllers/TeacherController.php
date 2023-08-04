<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use Illuminate\Support\Arr;
use App\Laravue\JsonResponse;

class TeacherController extends Controller
{
    const ITEM_PER_PAGE = 1000;

    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = $request->get('keyword');;
        $subjects = Teacher::where('name', 'like', '%'.$keyword.'%')
        ->paginate($limit);
        //dd(DB::getQueryLog()); // Show results of log
        return response()->json(new JsonResponse(['teachers' => $subjects]));
    }

    public function store(Request $request)
    {
        $teacher = Teacher::create($request->all());
        return response()->json(new JsonResponse(['teacher' => $teacher]));
    }

    public function show(Teacher $teacher)
    {
        return response()->json(new JsonResponse(['teacher' => $teacher]));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $input = $request->all();
        $teacher->fill($input)->save();
        return response()->json(new JsonResponse(['teacher' => $teacher]));
    }

    public function destroy($id)
    {
        Teacher::destroy($id);
        return response()->json(new JsonResponse(['msg' => 'Deleted successfully.']));
    }
}
