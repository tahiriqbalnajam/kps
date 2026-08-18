<?php

namespace App\Http\Controllers;

use App\Laravue\JsonResponse;
use App\Models\Classes;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ClassesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $keyword = $request->keyword;
        $include = $request->include;

        // Count only active students of the selected session (fall back to the active session)
        $sessionId = $request->input('session_id');
        if (empty($sessionId)) {
            $sessionId = ClassSession::getActive()?->id;
        }
        $activeStudentFilter = function ($query) use ($sessionId) {
            $query->where('status', 'enable')
                ->when($sessionId, fn ($q) => $q->where('session_id', $sessionId));
        };

        $query = Classes::select('classes.*')
            ->withCount([
                'students' => function ($query) use ($activeStudentFilter) {
                    $activeStudentFilter($query);
                },
                'students as males_count' => function ($query) use ($activeStudentFilter) {
                    $activeStudentFilter($query);
                    $query->where('gender', 'male');
                },
                'students as females_count' => function ($query) use ($activeStudentFilter) {
                    $activeStudentFilter($query);
                    $query->where('gender', 'female');
                },
            ])
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('classes.name', 'like', '%'.$keyword.'%');
            })
            // Default to enabled classes; pass status=disable or status=all to widen
            ->when($request->get('status') !== 'all', function ($query) use ($request) {
                return $query->where('classes.status', $request->get('status') ?: 'enable');
            });

        // Include sections relationship when requested
        if ($include === 'sections') {
            $query->with(['sections' => function ($query) use ($activeStudentFilter, $request) {
                // Default to enabled sections (tree-selects in student forms,
                // attendance, exams...); pass sections_status=all to see disabled
                // ones too (classlist management page for re-enabling).
                $query->when($request->get('sections_status') !== 'all', function ($q) {
                    $q->where('sections.status', 'enable');
                });
                $query->withCount([
                    'students' => function ($query) use ($activeStudentFilter) {
                        $activeStudentFilter($query);
                    },
                    'students as males_count' => function ($query) use ($activeStudentFilter) {
                        $activeStudentFilter($query);
                        $query->where('gender', 'male');
                    },
                    'students as females_count' => function ($query) use ($activeStudentFilter) {
                        $activeStudentFilter($query);
                        $query->where('gender', 'female');
                    },
                ]);
            }]);
        }

        $classes = $query->paginate($request->input('limit', 30));

        return response()->json(new JsonResponse(['classes' => $classes]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $class = new Classes;
        $class->name = $request->name;
        $class->priority = $request->input('priority', 0);
        $class->status = $request->input('status', 'enable');
        $class->save();

        return response()->json(new JsonResponse(['class' => $class]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Classes $class)
    {

        return response()->json(new JsonResponse(['class' => $class]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $class = Classes::find($id);
        $class->name = $request->name;
        if ($request->has('priority')) {
            $class->priority = $request->priority;
        }
        if ($request->has('status')) {
            $class->status = $request->status;
        }
        $class->save();

        return response()->json(new JsonResponse(['class' => $class]));
    }

    /**
     * Toggle a class between enabled and disabled.
     *
     * @return Response
     */
    public function toggleStatus(Classes $class)
    {
        $class->status = $class->status === 'disable' ? 'enable' : 'disable';
        $class->save();

        return response()->json(new JsonResponse(['class' => $class]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(Classes $class)
    {
        $class->delete();

        return response()->json(new JsonResponse('Deleted successfully'));
    }

    /**
     * Bulk update priorities for multiple classes
     *
     * @return Response
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
                'count' => count($updates),
            ]));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to update priorities: '.$e->getMessage()], 500);
        }
    }
}
