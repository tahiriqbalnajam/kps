<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
class ClassesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keyword = $request->keyword;
        $include = $request->include;
        
        $query = Classes::select('classes.*')
            ->withCount([
                'students',
                'students as males_count' => function ($query) {
                    $query->where('gender', 'male');
                },
                'students as females_count' => function ($query) {
                    $query->where('gender', 'female');
                }
            ])
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('classes.name', 'like', '%' . $keyword . '%');
            });
        
        // Include sections relationship when requested
        if ($include === 'sections') {
            $query->with(['sections' => function($query) {
                $query->withCount([
                    'students',
                    'students as males_count' => function ($query) {
                        $query->where('gender', 'male');
                    },
                    'students as females_count' => function ($query) {
                        $query->where('gender', 'female');
                    }
                ]);
            }]);
        }
        
        $classes = $query->paginate($request->input('limit', 30));

        return response()->json(new JsonResponse(['classes' => $classes]));
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
        $class = new Classes();
        $class->name = $request->name;
        $class->priority = $request->input('priority', 0);
        $class->save();
        return response()->json(new JsonResponse(['class' => $class]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Classes $class)
    {

        return response()->json(new JsonResponse(['class' => $class]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $class = Classes::find($id);
        $class->name = $request->name;
        if ($request->has('priority')) {
            $class->priority = $request->priority;
        }
        $class->save();
        return response()->json(new JsonResponse(['class' => $class]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Classes $class)
    {
        $class->delete();
        return response()->json(new JsonResponse('Deleted successfully'));
    }

    /**
     * Bulk update priorities for multiple classes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdatePriority(Request $request)
    {
        $updates = $request->input('classes', []);
        
        if (empty($updates)) {
            return response()->json(['error' => 'No classes provided'], 400);
        }

        try {
            DB::beginTransaction();
            
            foreach ($updates as $update) {
                if (isset($update['id']) && isset($update['priority'])) {
                    Classes::where('id', $update['id'])
                        ->update(['priority' => $update['priority']]);
                }
            }
            
            DB::commit();
            
            return response()->json(new JsonResponse([
                'message' => 'Priorities updated successfully',
                'count' => count($updates)
            ]));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update priorities: ' . $e->getMessage()], 500);
        }
    }
}
