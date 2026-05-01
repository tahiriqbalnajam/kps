<?php

namespace App\Http\Controllers;

use App\Models\SyllabusChapter;
use App\Models\SyllabusTopic;
use App\Models\Classes;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;

class SyllabusController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getSubjectsByClass($classId)
    {
        $class = Classes::with('subjects')->find($classId);
        $subjects = $class ? $class->subjects : [];
        return response()->json(new JsonResponse(['subjects' => $subjects]));
    }

    // ── Chapters ──────────────────────────────────────────────────────────────

    public function getChapters(Request $request)
    {
        $chapters = SyllabusChapter::with('topics')
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->orderBy('order_no')
            ->get();

        return response()->json(new JsonResponse(['chapters' => $chapters]));
    }

    public function storeChapter(Request $request)
    {
        $maxOrder = SyllabusChapter::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->max('order_no') ?? 0;

        $chapter = SyllabusChapter::create(array_merge($request->all(), ['order_no' => $maxOrder + 1]));
        $chapter->load('topics');
        return response()->json(new JsonResponse(['chapter' => $chapter]));
    }

    public function updateChapter(Request $request, $id)
    {
        $chapter = SyllabusChapter::findOrFail($id);
        $chapter->update($request->all());
        $chapter->load('topics');
        return response()->json(new JsonResponse(['chapter' => $chapter]));
    }

    public function destroyChapter($id)
    {
        SyllabusChapter::destroy($id);
        return response()->json(new JsonResponse(['msg' => 'Deleted']));
    }

    // ── Topics ────────────────────────────────────────────────────────────────

    public function storeTopic(Request $request)
    {
        $maxOrder = SyllabusTopic::where('chapter_id', $request->chapter_id)->max('order_no') ?? 0;
        $topic = SyllabusTopic::create(array_merge($request->all(), ['order_no' => $maxOrder + 1]));
        return response()->json(new JsonResponse(['topic' => $topic]));
    }

    public function updateTopic(Request $request, $id)
    {
        $topic = SyllabusTopic::findOrFail($id);
        $topic->update($request->all());
        return response()->json(new JsonResponse(['topic' => $topic]));
    }

    public function destroyTopic($id)
    {
        SyllabusTopic::destroy($id);
        return response()->json(new JsonResponse(['msg' => 'Deleted']));
    }

    public function toggleTopic($id)
    {
        $topic = SyllabusTopic::findOrFail($id);
        $completed = !$topic->completed;
        $topic->update([
            'completed'      => $completed,
            'completed_date' => $completed ? now()->toDateString() : null,
        ]);
        return response()->json(new JsonResponse(['topic' => $topic]));
    }

    // ── Report ────────────────────────────────────────────────────────────────

    public function report(Request $request)
    {
        $chapters = SyllabusChapter::with(['topics', 'subject', 'classModel'])
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->orderBy('class_id')->orderBy('subject_id')->orderBy('order_no')
            ->get();

        $today = now()->startOfDay();

        $report = $chapters
            ->groupBy(fn($c) => $c->class_id . '_' . $c->subject_id)
            ->map(function ($chapters) use ($today) {
                $totalTopics     = $chapters->sum(fn($c) => $c->topics->count());
                $completedTopics = $chapters->sum(fn($c) => $c->topics->where('completed', true)->count());
                $totalChapters   = $chapters->count();
                $completedChapters = $chapters->filter(
                    fn($c) => $c->topics->count() > 0 && $c->topics->every(fn($t) => $t->completed)
                )->count();

                $chapterData = $chapters->map(function ($chapter) use ($today) {
                    $total     = $chapter->topics->count();
                    $completed = $chapter->topics->where('completed', true)->count();

                    if ($total === 0) {
                        $status = 'empty';
                    } elseif ($completed === $total) {
                        $status = 'completed';
                    } elseif ($chapter->planned_end_date && $today->gt($chapter->planned_end_date)) {
                        $status = 'behind';
                    } elseif ($chapter->planned_start_date && $today->lt($chapter->planned_start_date)) {
                        $status = 'upcoming';
                    } elseif ($completed > 0) {
                        $status = 'in_progress';
                    } else {
                        $status = 'not_started';
                    }

                    return [
                        'id'                 => $chapter->id,
                        'title'              => $chapter->title,
                        'planned_start_date' => $chapter->planned_start_date?->format('Y-m-d'),
                        'planned_end_date'   => $chapter->planned_end_date?->format('Y-m-d'),
                        'total_topics'       => $total,
                        'completed_topics'   => $completed,
                        'status'             => $status,
                    ];
                })->values();

                return [
                    'class_id'           => $chapters->first()->class_id,
                    'class_name'         => $chapters->first()->classModel->name ?? '',
                    'subject_id'         => $chapters->first()->subject_id,
                    'subject_title'      => $chapters->first()->subject->title ?? '',
                    'total_chapters'     => $totalChapters,
                    'completed_chapters' => $completedChapters,
                    'total_topics'       => $totalTopics,
                    'completed_topics'   => $completedTopics,
                    'completion_percent' => $totalTopics > 0
                        ? (int) round(($completedTopics / $totalTopics) * 100)
                        : 0,
                    'chapters'           => $chapterData,
                ];
            })->values();

        return response()->json(new JsonResponse(['report' => $report]));
    }
}
