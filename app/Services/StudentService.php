<?php
namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\Student;
use App\Models\TestResult;
use Illuminate\Support\Arr;
use App\Models\StudentAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\Contracts\StudentServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentService implements StudentServiceInterface
{
    const ITEM_PER_PAGE = 1000;

    public function listStudents(array $searchParams)
    {
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        
        $query = QueryBuilder::for(Student::class)
            ->allowedFields(['id','user_id','roll_no','name','adminssion_number','parent_id',
                'class_id','session_id','dob','doa','b_form','gender',
                'is_orphan','cast','previous_school','monthly_fee','sibling','religion',
                'pef_admission','nadra_pending','action_required','action_details','status',
                'parents.id','parents.name','parent.phone','stdclasses.id','stdclasses.name'])
            ->with('parents', 'stdclasses', 'class_session')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'name', 'roll_no', 'adminssion_number', 'is_orphan', 
                'pef_admission', 'nadra_pending', 'gender', 'status',
                AllowedFilter::partial('parent_phone', 'parents.phone'),
                AllowedFilter::partial('parent_name', 'parents.name'),
                AllowedFilter::exact('stdclass', 'stdclasses.id'),
                AllowedFilter::exact('section_id', 'section_id'),
                // Add 'all' filter for searching across multiple fields
                AllowedFilter::callback('all', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('name', 'LIKE', "%{$value}%")
                          ->orWhere('adminssion_number', 'LIKE', "%{$value}%")
                          ->orWhere('roll_no', 'LIKE', "%{$value}%")
                          ->orWhere('b_form', 'LIKE', "%{$value}%")
                          ->orWhereHas('parents', function ($parentQuery) use ($value) {
                              $parentQuery->where('name', 'LIKE', "%{$value}%")
                                          ->orWhere('phone', 'LIKE', "%{$value}%");
                          });
                    });
                }),
                // Add custom age filter
                AllowedFilter::callback('age_less_than', function ($query, $value) {
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) < ?', [$value]);
                }),
                AllowedFilter::callback('age_greater_than', function ($query, $value) {
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) > ?', [$value]);
                }),
                // Add DOB date range filters
                AllowedFilter::callback('dob_from', function ($query, $value) {
                    $query->where('dob', '>=', $value);
                }),
                AllowedFilter::callback('dob_to', function ($query, $value) {
                    $query->where('dob', '<=', $value);
                }),
                // Add NADRA B-Form verification filter
                AllowedFilter::callback('nadra_b_form_verified', function ($query, $value) {
                    if ($value === 'No') {
                        $query->where(function ($q) {
                            $q->whereNull('b_form')
                              ->orWhere('b_form', '')
                              ->orWhere('b_form', 'N/A');
                        });
                    } else {
                        $query->where('b_form', '!=', '')
                              ->whereNotNull('b_form')
                              ->where('b_form', '!=', 'N/A');
                    }
                })
            ]);

        // Add default status filter if no status filter is provided
        if (!request()->has('filter.status')) {
            $query->where('status', 'enable');
        }

        // Order by roll_no ascending
        $query->orderBy('roll_no', 'asc');

        return $query->paginate($limit)
            ->appends(request()->query());
    }

    public function getStudentsQueryForExport(Request $request)
    {
        // Convert the flat filter parameters to nested format that QueryBuilder expects
        $requestData = $request->all();
        $filterData = [];
        
        foreach ($requestData as $key => $value) {
            if (preg_match('/^filter\[(.+)\]$/', $key, $matches)) {
                $filterData[$matches[1]] = $value;
            }
        }
        
        // Create a properly formatted request for QueryBuilder
        $formattedRequest = new Request(['filter' => $filterData]);
        
        // Create QueryBuilder with the formatted request
        $query = QueryBuilder::for(Student::class, $formattedRequest)
            ->allowedFields(['id','user_id','roll_no','name','adminssion_number','parent_id',
                'class_id','session_id','dob','doa','b_form','gender',
                'is_orphan','cast','previous_school','monthly_fee','sibling','religion',
                'pef_admission','nadra_pending','action_required','action_details','status',
                'parents.id','parents.name','parent.phone','stdclasses.id','stdclasses.name'])
            ->with('parents', 'stdclasses', 'class_session')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'name', 'roll_no', 'adminssion_number', 'is_orphan', 
                'pef_admission', 'nadra_pending', 'gender', 'status',
                AllowedFilter::partial('parent_phone', 'parents.phone'),
                AllowedFilter::partial('parent_name', 'parents.name'),
                AllowedFilter::exact('stdclass', 'stdclasses.id'),
                AllowedFilter::exact('section_id', 'section_id'),
                // Add 'all' filter for searching across multiple fields
                AllowedFilter::callback('all', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('name', 'LIKE', "%{$value}%")
                          ->orWhere('adminssion_number', 'LIKE', "%{$value}%")
                          ->orWhere('roll_no', 'LIKE', "%{$value}%")
                          ->orWhere('b_form', 'LIKE', "%{$value}%")
                          ->orWhereHas('parents', function ($parentQuery) use ($value) {
                              $parentQuery->where('name', 'LIKE', "%{$value}%")
                                          ->orWhere('phone', 'LIKE', "%{$value}%");
                          });
                    });
                }),
                // Add custom age filter
                AllowedFilter::callback('age_less_than', function ($query, $value) {
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) < ?', [$value]);
                }),
                AllowedFilter::callback('age_greater_than', function ($query, $value) {
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) > ?', [$value]);
                }),
                // Add DOB date range filters
                AllowedFilter::callback('dob_from', function ($query, $value) {
                    $query->where('dob', '>=', $value);
                }),
                AllowedFilter::callback('dob_to', function ($query, $value) {
                    $query->where('dob', '<=', $value);
                }),
                // Add NADRA B-Form verification filter
                AllowedFilter::callback('nadra_b_form_verified', function ($query, $value) {
                    if ($value === 'No') {
                        $query->where(function ($q) {
                            $q->whereNull('b_form')
                              ->orWhere('b_form', '')
                              ->orWhere('b_form', 'N/A');
                        });
                    } else {
                        $query->where('b_form', '!=', '')
                              ->whereNotNull('b_form')
                              ->where('b_form', '!=', 'N/A');
                    }
                })
            ]);

        // Add default status filter if no status filter is provided
        if (!isset($filterData['status'])) {
            $query->where('status', 'enable');
        }

        return $query;
    }

    public function storeStudent(array $data)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'name' => 'required|string|max:191',
            'b_form' => 'nullable|string|max:191',
            'adminssion_number' => 'nullable|string|max:15',
            'roll_no' => 'nullable|string|max:191',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        try {
            DB::beginTransaction();
            //commmented as user creation is not required here, user will be created in parent controller
            // $user['name'] = $data['name'];
            // $user['email'] = $data['name'].rand(10,100).'@idlschool.com';
            // $user['password'] = bcrypt('idl123');
            // $user = User::create($user);
            // $role = Role::findByName('student');
            // $user->syncRoles($role);
            // $data['user_id'] = $user->id;
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
            DB::raw('(test_results.score / tests.total_marks) * 100 as percentage')
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