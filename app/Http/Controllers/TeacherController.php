<?php

namespace App\Http\Controllers;

use App\Laravue\JsonResponse;
use App\Models\Log;
use App\Models\Role;
use App\Models\Settings;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeacherSalary;
use App\Models\User;
use App\Traits\TransactionTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    use TransactionTrait;

    const ITEM_PER_PAGE = 1000;

    private $column_select = ['id', 'class_id', 'name', 'designation', 'teacher_special_id', 'father_name', 'father_cnic',
        'doj', 'education', 'experience', 'gender',
        'pay', 'cnic', 'address', 'phone', 'status', 'dob'];

    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = $request->get('keyword');
        $filtercol = $request->get('filtercol');

        $all = ($request->get('filtercol') == 'all') ? true : false;
        $data = Teacher::select(array_merge(
            array_map(fn ($column) => 'teachers.'.$column, $this->column_select),
            ['cl.name as class_name']
        ))
            ->leftJoin('classes as cl', 'cl.id', '=', 'teachers.class_id')
        // Default to active teachers; pass status=inactive or status=all to
        // include inactive ones in the list
            ->when($request->filled('status') && $request->get('status') !== 'all', function ($query) use ($request) {
                return $query->where('teachers.status', $request->get('status'));
            }, function ($query) {
                return $query->where('teachers.status', 'active');
            })
        // ->where('name', 'like', '%'.$keyword.'%')
            ->when($all || ($filtercol == 'name' && ! empty($keyword)), function ($query) use ($all, $keyword) {
                if ($all) {
                    return $query->orWhere('teachers.name', 'like', '%'.$keyword.'%');
                } else {
                    return $query->where('teachers.name', 'like', '%'.$keyword.'%');
                }
            })
            ->when($all || ($filtercol == 'cnic' && ! empty($keyword)), function ($query) use ($all, $keyword) {
                if ($all) {
                    return $query->orWhere('teachers.cnic', 'like', '%'.$keyword.'%');
                } else {
                    return $query->where('teachers.cnic', 'like', '%'.$keyword.'%');
                }
            })
            ->when($all || ($filtercol == 'phone' && ! empty($keyword)), function ($query) use ($all, $keyword) {
                if ($all) {
                    return $query->orWhere('teachers.phone', 'like', '%'.$keyword.'%');
                } else {
                    return $query->where('teachers.phone', 'like', '%'.$keyword.'%');
                }
            })
            ->paginate($limit);
        // dd(DB::getQueryLog()); // Show results of log
        $this->attachTestAverages($data);

        return response()->json(new JsonResponse(['teachers' => $data]));
    }

    /**
     * Attach each teacher's overall test average (mean of per-test
     * percentages, same definition as the profile page) to the paginated rows.
     */
    private function attachTestAverages($data)
    {
        if ($data->isEmpty()) {
            return;
        }

        $averages = DB::table('tests as t')
            ->join('test_results as tr', 't.id', '=', 'tr.test_id')
            ->whereIn('t.teacher_id', $data->pluck('id'))
            ->where('tr.absent', 'no')
            ->selectRaw('t.teacher_id, t.id, AVG(tr.score) / t.total_marks * 100 as pct')
            ->groupBy('t.id', 't.teacher_id', 't.total_marks')
            ->get()
            ->groupBy('teacher_id')
            ->map(fn ($rows) => round($rows->avg('pct'), 1));

        $data->getCollection()->each(function ($teacher) use ($averages) {
            $teacher->avg_pct = $averages->get($teacher->id);
        });
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required'],
                'cnic' => 'required',
            ]
        );

        if ($validator->fails()) {
            return responseFailed($validator->errors()->first(), '500');
        } else {
            $params = $request->all();
            DB::beginTransaction();
            $user['name'] = $request->name;
            $user['email'] = $request->name.rand(10, 100).'@idlschool.com';
            $user['password'] = bcrypt($request['password']);
            $user = User::create($user);
            $teacher = Teacher::create($request->all() + ['user_id' => $user->id]);
            $role = Role::findByName('teacher');
            $user->syncRoles($role);
            $loginUser = Auth::user();
            DB::commit();

            return response()->json(new JsonResponse(['teacher' => $teacher]));
        }

    }

    public function show($id)
    {
        $teacher = Teacher::select($this->column_select)->where('id', $id)->get();

        return response()->json(new JsonResponse(['teacher' => $teacher]));
    }

    public function update(Request $request, $id)
    {
        $user = Teacher::where('id', $id)->update($request->all());

        return response()->json(new JsonResponse(['teacher' => $user]));
    }

    public function destroy($id)
    {
        User::destroy($id);

        return response()->json(new JsonResponse(['msg' => 'Deleted successfully.']));
    }

    public function save_salary(Request $request)
    {

        $validated = $request->validate([
            'salaries' => 'required|array',
            'salaries.*.teacher_id' => 'required|integer|exists:teachers,id',
            'salaries.*.salary' => 'required|numeric',
            'salaries.*.month' => 'required|date',
            'salaries.*.present_days' => 'required|integer',
            'salaries.*.absent_days' => 'required|integer',
            'salaries.*.allow_leaves' => 'required|integer',
            'salaries.*.payable_days' => 'required|integer',
            'salaries.*.daily_salary' => 'required|integer',
            'salaries.*.total_pay' => 'required|integer',
            'salaries.*.fine' => 'required|integer',
            'salaries.*.bonus' => 'required|integer',
            'salaries.*.paid' => 'required|integer',
            'salaries.*.previous_balance' => 'required|integer',
            'salaries.*.balance' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            // Loop through each salary data and create a new TeacherSalary record
            foreach ($validated['salaries'] as $salaryData) {
                // fiend teacher and get user id, and with this user id save a transaction of balance
                $teacher = Teacher::find($salaryData['teacher_id']);
                $jama_account = $teacher->user_id;
                $naam_account = 1;
                $amount = $salaryData['balance'];
                $comment = 'salary for teacher '.$teacher->name.' for month '.$salaryData['month'];

                $this->doTransaction($naam_account, $jama_account, $amount, 'others', '', $comment);

                TeacherSalary::create([
                    'teacher_id' => $salaryData['teacher_id'],
                    'salary' => $salaryData['salary'],
                    'month' => $salaryData['month'],
                    'present_days' => $salaryData['present_days'],
                    'absent_days' => $salaryData['absent_days'],
                    'allow_leaves' => $salaryData['allow_leaves'],
                    'payable_days' => $salaryData['payable_days'],
                    'estimated_salary' => $salaryData['total_pay'],
                    'fine' => $salaryData['fine'],
                    'bonus' => $salaryData['bonus'],
                    'paid' => $salaryData['paid'],
                    'previous_balance' => $salaryData['previous_balance'],
                    'balance' => $salaryData['balance'],
                ]);
            }

            DB::commit();

            return response()->json(new JsonResponse(['salaries' => 'saved successfully']));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(new JsonResponse(['error' => $e->getMessage()]));

        }

    }

    public function find_already_saved_salary(Request $request)
    {
        $date = $request->input('month');
        $month = date('Y-m-d', strtotime($date));
        $salaries = TeacherSalary::where('month', $month)
            ->count();

        if ($salaries) {
            return response()->json(new JsonResponse(['salaries' => $salaries]));
        } else {
            return response()->json(new JsonResponse(['message' => 'No salaries found for this month.']));
        }
    }

    public function calculateAllTeachersPay(Request $request)
    {
        $month = $request->input('month') ? date('m', strtotime($request->input('month'))) : date('m');
        $year = $request->input('month') ? date('Y', strtotime($request->input('month'))) : date('Y');
        $allowedLeaves = $request->input('allowed_leaves', 1);

        $teachers = Teacher::where('status', 'active')->get();
        $pay = [];
        foreach ($teachers as $teacher) {
            $teacher_details = ['teacher_id' => $teacher->id, 'name' => $teacher->name, 'month' => $request->input('month'),
                'salary' => $teacher->pay, 'allow_leaves' => $allowedLeaves];
            $pay_details = $teacher->calculatePay($month, $year, $allowedLeaves, $teacher->pay);
            $pay[] = array_merge($teacher_details, $pay_details);
        }

        return response()->json(new JsonResponse(['pay' => $pay]));
    }

    public function calculateTeacherPay(Request $request, $teacherId)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $allowedLeaves = $request->input('allowed_leaves', 2);

        $teacher = Teacher::findOrFail($teacherId);
        $pay = $teacher->calculatePay($month, $year, $allowedLeaves);

        return response()->json(['pay' => $pay]);
    }

    public function get_tests(Request $request, $teacherId)
    {
        $teacher = Teacher::findOrFail($teacherId);
        $tests = $teacher->getTestsWithAverageMarks();
        $groupedTests = $tests->groupBy('class_name')->map(function ($classTests, $className) {
            return [
                'class' => $className,
                'subjects' => $classTests->groupBy('subject_title')->map(function ($subjectTests) {
                    return $subjectTests->map(function ($test) {
                        return [
                            'test_title' => $test->test_title,
                            'total_marks' => $test->total_marks,
                            'average_marks' => $test->average_marks,
                            'percent' => $test->percent,
                        ];
                    });
                }),
            ];
        })->values();

        return response()->json(new JsonResponse(['tests' => $groupedTests]));

    }

    public function online_attendance(Request $request, $teacherId)
    {
        $teacher = Teacher::findOrFail($teacherId);
        $alreadydone = $teacher->alreadyAttDone();
        if ($alreadydone) {
            return response()->json(new JsonResponse([], 'Today\'s attendance has already been submitted'));
        }

        // Get school opening time from settings
        $settings = Settings::where('setting_key', 'opening_time')->first();
        $openingTime = $settings ? $settings->setting_value : '09:00';

        $currentDateTime = Carbon::now();
        $attendance = new TeacherAttendance;
        $attendance->teacher_id = $teacherId;
        $attendance->status = 'present';
        $attendance->attendance_date = $currentDateTime;
        $attendance->opening_time = $openingTime; // Compare with opening time
        $attendance->save();

        return response()->json(new JsonResponse(['attendance' => $attendance]));
    }
}
