<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\examStudentResult;
use App\Models\Classes;
use Illuminate\Support\Arr;
use App\Laravue\JsonResponse;
use App\Models\ExamsReults;

use DB;

class ExamResultController extends Controller
{
    const ITEM_PER_PAGE = 1000;
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = $request->get('keyword');
        $keywords = $request->get('keywords');
        $filtercol = $request->get('filtercol');
        

        $students = '';
        if($filtercol == 'exams'){
            $students = Exam::where('examname', 'like', '%'.$keyword.'%')
            ->paginate($limit);
        }

        if($filtercol == 'student'){
            $students = Exams::with('student')
            ->when(($filtercol == 'students' && !empty($keyword)), function ($query) use ($keyword) {
            $students = Exams::with('student')->where('student.id', 'like', '%'.$keywords.'%');
               // $students = AddExam::where('examname', 'like', '%'.$keyword.'%');
        })
        
        
        
        ->paginate($limit);

        }

        
    /// $students = AddExamsReults::where('exam_id', 'like', '%'.$keyword.'%')


        

        //dd(DB::getQueryLog()); // Show results of log
        return response()->json(new JsonResponse(['resource' => $students]));
    }


    public function store(Request $request)
    {
        $exam = Exam::create($request->all());
        print_r($exam);
        $students = $request->students;
        $exam_id = $exam->id;
        $stuents_array = array();
        foreach($students as $student) 
            $stuents_array[] = array('exam_id' => $exam_id, 'student_id' => $student['id'], 'class_id' => $student['class_id'], 'total_marks' => $exam->total_marks, 'obtained_marks' => $student['obtained_marks'] );
            
        $result_exam_student= ExamsReults::insert($stuents_array);
        return response()->json(new JsonResponse(['examsreult' => $stuents_array]));
    }


    public function show(ExamsReults $AddExamsReults)
    {
        return response()->json(new JsonResponse(['AddExamsReults' => $AddExamsReults]));
    }
   

}
