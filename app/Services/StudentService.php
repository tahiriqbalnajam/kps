<?php
namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\Student;
use App\Models\TestResult;
use Illuminate\Support\Arr;
use App\Models\StudentAttendance;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\Contracts\StudentServiceInterface;

class StudentService implements StudentServiceInterface
{
    const ITEM_PER_PAGE = 1000;

    public function listStudents(array $searchParams)
    {
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        return QueryBuilder::for(Student::class)
            ->allowedFields(['id','user_id','roll_no','name','adminssion_number','parent_id','class_id','session_id','dob','doa','b_form','gender',
                                'is_orphan','cast','previous_school','monthly_fee','sibling','religion','pef_admission','nadra_pending',
                                'action_required','action_details','status','parents.id','parents.name','parent.phone','stdclasses.id','stdclasses.name'])
            ->with('parents', 'stdclasses', 'class_session')
            ->allowedFilters([
                'id', 'name', 'roll_no', 'adminssion_number', 'is_orphan', 'pef_admission', 'nadra_pending', 'gender', 'status',
                AllowedFilter::partial('parent_phone', 'parents.phone'),
                AllowedFilter::partial('parent_name', 'parents.name'),
                AllowedFilter::exact('stdclass', 'stdclasses.id'),
            ])
            ->paginate($limit)
            ->appends(request()->query());
    }

    public function storeStudent(array $data)
    {
        try {
            DB::beginTransaction();
            $user['name'] = $data['name'];
            $user['email'] = $data['name'].rand(10,100).'@idlschool.com';
            $user['password'] = bcrypt('idl123');
            $user = User::create($user);
            $role = Role::findByName('student');
            $user->syncRoles($role);
            $data['user_id'] = $user->id;
            $student = Student::create($data);
            DB::commit();
            return $student;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateStudent(Student $student, array $data)
    {
        $student->fill($data)->save();
        return $student;
    }

    public function addAttendance(array $data)
    {
        $date = $data['date'];
        $students = $data['students'];
        foreach ($students as $studentData) {
            $attendance[] = [
                'class_id' => $studentData['class_id'],
                'student_id' => $studentData['id'],
                'status' => $studentData['attendance'],
                'created_at' => $date,
            ];
        }
        StudentAttendance::insert($attendance);
    }

    public function editClass(array $selectedStudents, $desiredClass)
    {
        foreach ($selectedStudents as $studentId) {
            Student::where('id', '=', $studentId)
                ->update(['class_id' => $desiredClass]);
        }
    }

    public function getSubjectWiseScores($studentId)
    {
        $results = TestResult::where('student_id', $studentId)
        ->join('tests', 'test_results.test_id', '=', 'tests.id')
        ->join('subjects', 'tests.subject_id', '=', 'subjects.id')
        ->select(
            'subjects.title as subject',
            'tests.id as test_id',
            'tests.date as test_date',
            'tests.total_marks',
            'test_results.score',
            \DB::raw('(test_results.score / tests.total_marks) * 100 as percentage')
        )
        ->get()
        ->groupBy('subject'); // Group results by subject

        // Structure the results
        $structuredResults = $results->map(function($tests, $subject) {
            $totalScore = $tests->sum('score');
            $totalMarks = $tests->sum('total_marks');
            if ($totalMarks == 0) {
                $overallPercentage = 0;
            } else {
                $overallPercentage = ($totalScore / $totalMarks) * 100;
            }

            return [
                'subject' => $subject,
                'overall_percentage' => $overallPercentage,
                'tests' => $tests->map(function($test) {
                    return [
                        'test_id' => $test->test_id,
                        'test_date' => $test->test_date,
                        'total_marks' => $test->total_marks,
                        'score' => $test->score,
                        'percentage' => $test->percentage,
                    ];
                })
            ];
        });

        return $structuredResults;
    }
}