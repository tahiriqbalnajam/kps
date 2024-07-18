<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Role;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Permission;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Models\TeacherSalary;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    const ITEM_PER_PAGE = 1000;

    private $column_select = array('id','class_id', 'name', 'father_name','father_cnic',
                                    'doj','education', 'experience', 'gender',
                                    'pay', 'cnic', 'address', 'phone','status');

    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = $request->get('keyword');;
        $filtercol = $request->get('filtercol');
        
        $all = ($request->get('filtercol') == 'all') ? true : false;
        $data = Teacher::select($this->column_select)
        //->where('name', 'like', '%'.$keyword.'%')
        ->when($all || ($filtercol == 'name' && !empty($keyword)), function ($query) use ($all, $keyword) {
            if($all)
                return $query->orWhere('name', 'like', '%' . $keyword . '%');
            else
                return $query->where('name', 'like', '%' . $keyword . '%');
        })
        ->when($all || ($filtercol == 'cnic' && !empty($keyword)), function ($query) use ($all, $keyword) {
            if($all)
                return $query->orWhere('cnic', 'like', '%' . $keyword . '%');
            else
                return $query->where('cnic', 'like', '%' . $keyword . '%');
        })
        ->when($all || ($filtercol == 'phone' && !empty($keyword)), function ($query) use ($all, $keyword) {
            if($all)
                return $query->orWhere('phone', 'like', '%' . $keyword . '%');
            else
                return $query->where('phone', 'like', '%' . $keyword . '%');
        })
        ->paginate($limit);
        //dd(DB::getQueryLog()); // Show results of log
        return response()->json(new JsonResponse(['teachers' => $data]));
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
            $user['email'] = $request->name.rand(10,100).'@idlschool.com';
            $user['password'] = bcrypt($request['password']);
            $user = User::create($user);
            $teacher = Teacher::create($request->all() + ['user_id' => $user->id]);
            $role = Role::findByName('teacher');
            $user->syncRoles($role);
            $loginUser = Auth::user();            
            DB::commit();
            return response()->json(new JsonResponse(['teacher' =>  $teacher]));
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


    public function save_salary(Request $request) {

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

        // Loop through each salary data and create a new TeacherSalary record
        foreach ($validated['salaries'] as $salaryData) {
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

         return response()->json(new JsonResponse(['salaries' => 'saved successfully']));
        
    }

    public function calculateAllTeachersPay(Request $request)
    {
        $month = $request->input('month') ? date('m', strtotime($request->input('month'))): date('m');
        $year = $request->input('month') ? date('Y', strtotime($request->input('month'))): date('Y');
        $allowedLeaves = $request->input('allowed_leaves', 1);

        $teachers = Teacher::all();
        $pay=array();
        foreach($teachers as $teacher){
            $teacher_details = array('teacher_id' => $teacher->id, 'name'=> $teacher->name, 'month' => $request->input('month'),
                                        'salary' => $teacher->pay, 'allow_leaves' => $allowedLeaves);
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
}
