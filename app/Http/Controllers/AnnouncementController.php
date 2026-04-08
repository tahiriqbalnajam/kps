<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::query()->orderByDesc('publish_date')->orderByDesc('created_at');

        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->target_audience) {
            $query->where('target_audience', $request->target_audience);
        }
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', (bool) $request->is_active);
        }
        if ($request->date_from) {
            $query->whereDate('publish_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('publish_date', '<=', $request->date_to);
        }

        $limit = $request->input('limit', 15);
        $items = $query->paginate($limit);

        return response()->json(new JsonResponse(['announcements' => $items]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'type'            => 'required|in:event,activity,notice,news,holiday,alert,circular',
            'content'         => 'required|string',
            'target_audience' => 'required|in:all,students,parents,teachers',
            'publish_date'    => 'required|date',
            'expiry_date'     => 'nullable|date|after_or_equal:publish_date',
            'is_active'       => 'boolean',
        ]);

        $announcement = Announcement::create([
            'title'           => $request->title,
            'type'            => $request->type,
            'content'         => $request->content,
            'target_audience' => $request->target_audience,
            'publish_date'    => Carbon::parse($request->publish_date)->format('Y-m-d'),
            'expiry_date'     => $request->expiry_date ? Carbon::parse($request->expiry_date)->format('Y-m-d') : null,
            'is_active'       => $request->input('is_active', true),
            'created_by'      => auth()->id(),
        ]);

        return response()->json(new JsonResponse(['announcement' => $announcement]), 201);
    }

    public function show($id)
    {
        $announcement = Announcement::findOrFail($id);
        return response()->json(new JsonResponse(['announcement' => $announcement]));
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $request->validate([
            'title'           => 'required|string|max:255',
            'type'            => 'required|in:event,activity,notice,news,holiday,alert,circular',
            'content'         => 'required|string',
            'target_audience' => 'required|in:all,students,parents,teachers',
            'publish_date'    => 'required|date',
            'expiry_date'     => 'nullable|date|after_or_equal:publish_date',
            'is_active'       => 'boolean',
        ]);

        $announcement->update([
            'title'           => $request->title,
            'type'            => $request->type,
            'content'         => $request->content,
            'target_audience' => $request->target_audience,
            'publish_date'    => Carbon::parse($request->publish_date)->format('Y-m-d'),
            'expiry_date'     => $request->expiry_date ? Carbon::parse($request->expiry_date)->format('Y-m-d') : null,
            'is_active'       => $request->input('is_active', $announcement->is_active),
        ]);

        return response()->json(new JsonResponse(['announcement' => $announcement->fresh()]));
    }

    public function toggle($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update(['is_active' => !$announcement->is_active]);
        return response()->json(new JsonResponse(['announcement' => $announcement->fresh()]));
    }

    public function destroy($id)
    {
        Announcement::findOrFail($id)->delete();
        return response()->json(new JsonResponse(['message' => 'Announcement deleted successfully']));
    }

    /**
     * Public endpoint: active announcements for mobile app (students/parents).
     */
    public function publicList(Request $request)
    {
        $query = Announcement::where('is_active', true)
            ->where('publish_date', '<=', now()->format('Y-m-d'))
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->format('Y-m-d'));
            })
            ->orderByDesc('publish_date')
            ->orderByDesc('created_at');

        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->target_audience && $request->target_audience !== 'all') {
            $query->whereIn('target_audience', ['all', $request->target_audience]);
        }

        $items = $query->limit($request->input('limit', 20))->get();

        return response()->json(new JsonResponse(['announcements' => $items]));
    }
}
