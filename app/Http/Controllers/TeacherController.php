<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Log;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

    public function calculateAllTeachersPay(Request $request)
    {
        $month = $request->input('month') ?? date('m');
        $year = $request->input('year') ?? date('Y');
        $allowedLeaves = $request->input('allowed_leaves', 1);

        $teachers = Teacher::all();
        $pay=array();
        foreach($teachers as $teacher){
            $teacher_details = array('name'=> $teacher->name, 'pay' => $teacher->pay, 'allow_leaves' => $allowedLeaves);
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
