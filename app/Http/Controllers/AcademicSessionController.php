<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Laravue\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AcademicSessionController extends Controller
{
    const ITEM_PER_PAGE = 20;

    /**
     * List all sessions, newest first.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $limit   = $request->get('limit', static::ITEM_PER_PAGE);

        $sessions = ClassSession::when($keyword, function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->orderBy('id', 'desc')
                    ->paginate($limit);

        return response()->json(new JsonResponse(['sessions' => $sessions]));
    }

    /**
     * Store a new session.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:150|unique:class_sessions,name',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:255',
        ]);

        $session = ClassSession::create([
            'name'        => $request->name,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'is_active'   => false,
            'description' => $request->description,
        ]);

        return response()->json(new JsonResponse(['session' => $session]));
    }

    /**
     * Show a single session.
     */
    public function show($id)
    {
        $session = ClassSession::findOrFail($id);
        return response()->json(new JsonResponse(['session' => $session]));
    }

    /**
     * Update a session.
     */
    public function update(Request $request, $id)
    {
        $session = ClassSession::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:150|unique:class_sessions,name,' . $id,
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:255',
        ]);

        $session->update([
            'name'        => $request->name,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'description' => $request->description,
        ]);

        return response()->json(new JsonResponse(['session' => $session->fresh()]));
    }

    /**
     * Delete a session. Active session cannot be deleted.
     */
    public function destroy($id)
    {
        $session = ClassSession::findOrFail($id);

        if ($session->is_active) {
            return response()->json(
                new JsonResponse(['error' => 'Cannot delete the active session. Set another session as active first.'], 'error'),
                422
            );
        }

        $studentCount = $session->students()->count();
        if ($studentCount > 0) {
            return response()->json(
                new JsonResponse(['error' => "Cannot delete: {$studentCount} student(s) are linked to this session."], 'error'),
                422
            );
        }

        $session->delete();
        return response()->json(new JsonResponse('Session deleted successfully'));
    }

    /**
     * Set a session as the active (current) session.
     * Only one session can be active at a time.
     */
    public function setActive($id)
    {
        $session = ClassSession::findOrFail($id);

        DB::transaction(function () use ($session) {
            // Deactivate all sessions first
            ClassSession::where('is_active', true)->update(['is_active' => false]);
            // Activate the selected session
            $session->update(['is_active' => true]);
        });

        return response()->json(new JsonResponse([
            'session' => $session->fresh(),
            'message' => "'{$session->name}' is now the active session.",
        ]));
    }

    /**
     * Return just the active session (used by other modules).
     */
    public function active()
    {
        $session = ClassSession::getActive();
        return response()->json(new JsonResponse(['session' => $session]));
    }
}
