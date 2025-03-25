<?php
namespace App\Http\Controllers;
use App\Models\Exam;
use App\Models\ExamSubject;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\Contracts\ExamServiceInterface;
use Spatie\QueryBuilder\QueryBuilder;

class ExamController extends Controller
{
    protected $examService;
    const ITEM_PER_PAGE = 1000;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(ExamServiceInterface $examService)
    {
        $this->examService = $examService;
    }

    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $exams = $this->examService->listExams($searchParams);
        return response()->json(new JsonResponse(['exams' => $exams]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'subjects' => 'required|array',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.total_marks' => 'required|integer',
        ]);

        $params = $request->all();
        $exam = $this->examService->storeExam($params);
        return response()->json(new JsonResponse(['exam' => $exam]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $exam = QueryBuilder::for(Exam::class)
            ->with('examSubjects.subject')
            ->allowedIncludes(['examResults.student'])
            ->findOrFail($id);
        
        return response()->json(new JsonResponse(['exam' => $exam]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        $request->validate([
            'title' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'subjects' => 'required|array',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.total_marks' => 'required|integer',
        ]);

        $params = $request->all();
        $exam = $this->examService->updateExam($id, $params);
        return response()->json(new JsonResponse(['exam' => $exam]));
    }
    // public function update(Request $request, $id){
    //     $keyword = $request->get('filtercol');
    //     $obtained_marks[] = array('obtained_marks' => $request->get('obtained_marks'));
    //     $obtained_marks = array_values($obtained_marks)[0];
    //     $examname[] = array('examname' => $request->get('examname'));
    //     $examname = array_values($examname)[0];
    //     if($keyword == 'update_exams'){
    //         $examresult = Exam::where('id', $id)->update($examname);
    //         return response()->json(new JsonResponse(['examresult' => $examresult]));
    //     }
    //     if($keyword == 'update_result'){
    //         $examresult = ExamResult::where('id', $id)->update($obtained_marks);
    //         return response()->json(new JsonResponse(['examresult' => $examresult]));
    //     }
    // }

    public function addExamMarks(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'marks' => 'required|array',
            'marks.*' => 'required|array',
            'marks.*.*' => 'required|integer|min:0',
        ]);

        $params = $request->all();
        $exam = $this->examService->addExamMarks($params);
        return response()->json(new JsonResponse(['exam' => $exam]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Exam::destroy($id);
        return response()->json(new JsonResponse(['msg' => 'Deleted successfully.']));
        //return response()->json(new JsonResponse(['msg' => 'Deleted successfully.']));
      // print_r($sffsdgd);
        //ExamResult::destroy($id);
        //return response()->json(new JsonResponse(['msg' => 'Deleted successfully.']));

        //$exam_id = $exam->id;
       // echo $id;
        //echo $exam_id;
        //echo $student_id;
      //  echo $class_id;
       // echo $total_marks;
      //  echo $obtained_marks;
      //  $stuents_array = array();
     //   foreach($students as $student) 
     //       $stuents_array[] = array('exam_id' => $exam_id, 'student_id' => $student_id, 'class_id' => $class_id, 'total_marks' => $total_marks, 'obtained_marks' => $obtained_marks );
    //    $result_exam_student= ExamResult::insert($stuents_array);
      //  return response()->json(new JsonResponse(['examsreult' => $stuents_array]));
    }



    public function getExamSubjects($examId)
    {
        $subjects = $this->examService->getExamSubjects($examId);
        return response()->json(new JsonResponse(['subjects' => $subjects]));
    }

    public function getExamById($examId)
    {
        $exam = $this->examService->getExamById($examId);
        return response()->json(new JsonResponse(['exam' => $exam]));
    }

    public function getExamReports($examId)
    {
        $reportData = $this->examService->getExamReports($examId);
        return response()->json(new JsonResponse($reportData));
    }
}
