<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\AddExam;
use App\Models\examStudentResult;
use App\Models\Classes;
use Illuminate\Support\Arr;
use App\Laravue\JsonResponse;
use App\Models\AddExamsReults;

class ExamResultController extends Controller
{
    const ITEM_PER_PAGE = 1000;
    public function store(Request $request)
    {
        $exam = AddExam::create($request->all());
        $students = $request->students;
        $exam_id = $exam->id;
        $stuents_array = array();
        foreach($students as $student) 
            $stuents_array[] = array('exam_id' => $exam_id, 'student_id' => $student['id'], 'class_id' => $student['class_id'], 'total_marks' => $exam->total_marks, 'obtained_marks' => $student['obtained_marks'] );
            
        $result_exam_student= AddExamsReults::insert($stuents_array);
        return response()->json(new JsonResponse(['examsreult' => $stuents_array]));
    }

    public function show(GetExams $GetExams)
    {
        return response()->json(new JsonResponse(['GetExams' => $GetExams]));
    }
   

}
