<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Parents;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\Contracts\ParentServiceInterface;

class ParentController extends Controller
{
    protected $parentService;
    const ITEM_PER_PAGE = 1000;

    public function __construct(ParentServiceInterface $parentService)
    {
        $this->parentService = $parentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $all = ($request->get('filtercol') == 'all') ? true : false;
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $filtercol = $request->get('filtercol');
        //DB::enableQueryLog(); // Enable query log
        $parents = Parents::with('students', 'user:id,name,email')->
                    when($keyword && !$filtercol, function ($query) use ($keyword) {
                       return $query->where('name', 'like', '%' . $keyword . '%');
                    })
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
        return response()->json(new JsonResponse(['parents' => $parents]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'address' => 'required|string',
            'profession' => 'required|string|max:255',
            'cnic' => 'required|string|max:15',
        ]);
        
        try {
            $parent = $this->parentService->createParent($validated);
            
            return response()->json(new JsonResponse(['parent' => $parent]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create parent: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Parents $parent)
    {
        return response()->json(new JsonResponse(['parent' => $parent]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Parents $parent)
    {
        //$parent
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'password' => 'sometimes|nullable|string|min:6',
            'address' => 'sometimes|required|string',
            'profession' => 'sometimes|required|string|max:255',
            'cnic' => 'sometimes|required|string|max:20',
        ]);
        
        try {
            $parent = $this->parentService->updateParent($id, $request->all());
            
            return response()->json(new JsonResponse(['parent' => $parent]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update parent: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a user account for a parent that doesn't have one.
     */
    public function createAccount(Request $request, $id)
    {
        $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $parent = Parents::findOrFail($id);

        if ($parent->user_id) {
            return response()->json(['success' => false, 'message' => 'Parent already has a user account.'], 422);
        }

        $user = User::create([
            'name'     => $parent->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $role = Role::findByName('parent');
        $user->syncRoles($role);

        $parent->user_id = $user->id;
        $parent->saveQuietly(); // skip boot events to avoid re-triggering user creation

        return response()->json(new JsonResponse(['user' => $user]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $parent = Parents::with('students')->findOrFail($id);

        if ($parent->students->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'This parent has ' . $parent->students->count() . ' child(ren) and cannot be deleted.',
            ], 422);
        }

        if ($parent->user_id) {
            User::destroy($parent->user_id);
        }

        $parent->delete();
        return response()->json(new JsonResponse('Deleted successfully'));
    }
}
