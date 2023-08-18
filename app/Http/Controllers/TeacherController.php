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

    private $column_select = array('id','name', 'gender', 'education', 'pay', 'dob', 'cnic', 'phone', 'address');

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
            $user = Teacher::create([
                'name' => $params['name'],
                'email' => 'teacher@school.test',
                'password' => Hash::make('teacher123'),
                'sex' => '0',
                'education' => $params['education'],
                'gender' => $params['gender'],
                'pay' => $params['pay'],
                'cnic' => $params['cnic'],
                'phone' => $params['phone'],
                'address' => $params['address'],
                'dob' => $params['dob'] ?? null,
                'description' => $params['description'] ?? ''
            ]);
            $role = Role::findByName('teacher');
            $user->syncRoles($role);
            $loginUser = Auth::user();            
            DB::commit();
            return response()->json(new JsonResponse(['teacher' => $user]));
        }
        
    }

    public function show($id)
    {
        $user = User::select($this->column_select)->where('id', $id)->get();
        return response()->json(new JsonResponse(['teacher' => $user]));
    }

    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)->update($request->all());
        return response()->json(new JsonResponse(['teacher' => $user]));
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(new JsonResponse(['msg' => 'Deleted successfully.']));
    }
}
