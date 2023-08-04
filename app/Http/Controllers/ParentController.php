<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Parents;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;

class ParentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $parents = Parents::
                    when($keyword, function ($query) use ($keyword) {
                        return $query->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->paginate(30);
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
        $parent = new Parents();
        $parent->name = $request->name;
        $parent->phone  = $request->phone;
        $parent->password = $request->password;
        $parent->address = $request->address;
        $parent->profession = $request->profession;
        $parent->cnic  = $request->cnic;
        $parent->save();
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
        $parent = Parents::find($id);
        $parent->name = $request->name;
        $parent->phone  = $request->phone;
        $parent->password = $request->password;
        $parent->address = $request->address;
        $parent->profession = $request->profession;
        $parent->cnic  = $request->cnic;
        $parent->save();
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
