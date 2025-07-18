<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Parents;
use Illuminate\Support\Arr;
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
        $parents = Parents::with('students')->
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
            'name' => 'required',
            'phone' => 'required',
            'password' => 'required',
            'address' => 'required',
            'profession' => 'required',
            'cnic' => 'required',
        ]);
        
        $parent = $this->parentService->createParent($validated);
        
        return response()->json(new JsonResponse(['parent' => $parent]));
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
        $parent = $this->parentService->updateParent($id, $request->all());
        
        return response()->json(new JsonResponse(['parent' => $parent]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Parent $parent)
    {
        $parent->delete();
        return response()->json(new JsonResponse('Deleted successfully'));
    }
}
