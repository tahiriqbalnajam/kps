<?php
namespace App\Http\Controllers;
use App\Models\Exam;
use App\Models\ExamResult;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;

class ExamTestController extends Controller
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
        $subjects = Exam::with('classes','results', 'results.student','results.subject')
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
            $exam_data = $request->all();
            //echo $examname = $exam_data['students']['id'];
            //print_r($request->all());die('asdf');
            $exam_data_array = array(
                'examname'=>$exam_data['examname'],
                'class_id'=>$exam_data['class_id'],
                'total_marks'=>$exam_data['total_marks'],
                'type'=>'test'
            );
            //print_r($exam_data_array);die('yes');
            $exam_id = Exam::create($exam_data_array);
            //echo $exam->id;
            //die('jkhk');
            foreach ($exam_data['students'] as $student) {
                foreach ($student as $key =>$value) {
                    if (preg_match('/^subject_(\d+)$/', $key, $matches)) {
                        $exam = new ExamResult(); // Create a new Exam model instance
                        $exam->exam_id = $exam_id->id;
                        $exam->class_id = $exam_data['class_id'];
                        $exam->student_id = $student['id']; // Assuming 'id' is the student's ID
                        $exam->subject_id = $matches[1]; // Assuming 'id' is the subject's ID
                        $exam->obtained_marks = $student['subject_'.$matches[1]]; // Assuming 'id' is the subject's ID
                        $exam->total_marks = $exam_data['total_marks'];
                        $exam->save();
                    }
                    // You can set other fields as needed
        
                    //$exam->save(); // Save the individual exam result to the database
                }
            }
            
            DB::commit();
            return response()->json(new JsonResponse(['examsreult' => $exam_data_array]));
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
    public function test_results($exam_id)
    {
        $classResults = DB::table('exams')
            ->join('exam_result_students', 'exams.id', '=', 'exam_result_students.exam_id')
            ->join('students', 'exam_result_students.student_id', '=', 'students.id')
            ->join('classes', 'exams.class_id', '=', 'classes.id')
            ->join('subjects', 'exam_result_students.subject_id', '=', 'subjects.id')
            ->where('exams.type','exam')
            ->select('exams.class_id','subjects.title as subject_name','classes.name as class_name','students.name as student_name', 'students.roll_no', 'exam_result_students.total_marks', 'exam_result_students.obtained_marks', 'exams.examname', 'exam_result_students.subject_id')
            ->orderBy('students.roll_no')
            ->orderBy('exams.class_id')
            ->get();

        return response()->json(new JsonResponse(['classResults' => $classResults]));
    }
}
