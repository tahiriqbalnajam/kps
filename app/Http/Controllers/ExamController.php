<?php
namespace App\Http\Controllers;
use App\Models\Exam;
use App\Models\ExamResult;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    const ITEM_PER_PAGE = 1000;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = $request->get('keyword');;
        //DB::enableQueryLog(); // Enable query log
        // $subjects = Exam::with(['results.student.subjects'])
        //     ->paginate($limit);
        $subjects = Exam::with('classes','results', 'results.student','results.subject');
        return response()->json(new JsonResponse(['exams' => $subjects]));
        $subjects = Exam::with(['classes', 'results' => function ($query) {
            $query->groupBy('student_id');
        }])
            //->with(['results.student', 'results.subject'])
            ->paginate($limit);
        //dd(DB::getQueryLog()); // Show results of log
        return response()->json(new JsonResponse(['exams' => $subjects]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $exam = Exam::create($request->all());
            $students = $request->students;
            $exam_id = $exam->id;
            $stuents_array = array();
            $student_id = '';
            $stuents_array = [];
            $studentIds = [];
            foreach ($students as $student) {
                $studentId = $student['id'];
                if (!in_array($studentId, $studentIds)) {
                    $stuents_array[] = array(
                        'exam_id' => $exam_id,
                        'student_id' => $studentId,
                        'class_id' => $student['class_id'],
                        'subject_id' => $request->subject_id,
                        'total_marks' => $exam->total_marks,
                    );
                    $studentIds[] = $studentId;
                }
            }

            foreach ($students  as $student) {
                foreach ($student as $key => $value) {
                    if (preg_match('/^subject_(\d+)$/', $key, $matches)) {
                        $examResult = new ExamResult([
                            'exam_id' => $exam->id,
                            'class_id' => $examData['class_id'],
                            'student_id' => $student['id'],
                            'subject_id' => $matches[1],
                            'obtained_marks' => $student['subject_'.$matches[1]],
                            'total_marks' => $this->searchTotalMarks( $examData['subjects'], $matches[1]),
                        ]);

                        $examResult->save();
                    }
                }
            }
   
            $result_exam_student= ExamResult::insert($stuents_array);
            DB::commit();
            return response()->json(new JsonResponse(['examsreult' => $stuents_array]));
        } catch (\Exception $ex) {
            DB::rollBack();
            return responseFailed($ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Exam $exam)
    {
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
        $keyword = $request->get('filtercol');
        $obtained_marks[] = array('obtained_marks' => $request->get('obtained_marks'));
        $obtained_marks = array_values($obtained_marks)[0];
        $examname[] = array('examname' => $request->get('examname'));
        $examname = array_values($examname)[0];
        if($keyword == 'update_exams'){
            $examresult = Exam::where('id', $id)->update($examname);
            return response()->json(new JsonResponse(['examresult' => $examresult]));
        }
        if($keyword == 'update_result'){
            $examresult = ExamResult::where('id', $id)->update($obtained_marks);
            return response()->json(new JsonResponse(['examresult' => $examresult]));
        }
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
}
