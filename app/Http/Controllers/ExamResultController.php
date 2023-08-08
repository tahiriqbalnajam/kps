<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddExam;
use App\Models\examStudentResult;

use App\Models\Classes;
use Illuminate\Support\Arr;
use App\Laravue\JsonResponse;

class ExamResultController extends Controller
{
    const ITEM_PER_PAGE = 1000;
    public function store(Request $request)
    {
        AddExam::create($request->all());
    }


    public function show(Classes $class)
    {
        return response()->json(new JsonResponse(['class' => $class]));
    }

   

   

}
