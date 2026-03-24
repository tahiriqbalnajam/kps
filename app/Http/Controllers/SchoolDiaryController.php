<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Section;
use App\Models\SchoolDiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Laravue\JsonResponse;
use Carbon\Carbon;

class SchoolDiaryController extends Controller
{
    /**
     * List all diary groups (class + section + date) with subject count.
     * Supports filters: class_id, section_id, diary_date, date_from, date_to
     */
    public function index(Request $request)
    {
        $query = SchoolDiary::query()
            ->select(
                'class_id', 'section_id', 'diary_date',
                DB::raw('COUNT(*) as subject_count'),
                DB::raw('MAX(created_at) as created_at'),
                DB::raw('MAX(created_by) as created_by')
            )
            ->groupBy('class_id', 'section_id', 'diary_date')
            ->orderByDesc('diary_date')
            ->orderBy('class_id')
            ->with([])   // eager load handled below after pagination
        ;

        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->section_id) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->diary_date) {
            $query->whereDate('diary_date', $request->diary_date);
        }
        if ($request->date_from) {
            $query->whereDate('diary_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('diary_date', '<=', $request->date_to);
        }

        $limit   = $request->input('limit', 30);
        $groups  = $query->paginate($limit);

        // Load class and section names in one shot
        $classIds   = $groups->pluck('class_id')->unique();
        $sectionIds = $groups->pluck('section_id')->unique();

        $classMap   = Classes::whereIn('id', $classIds)->pluck('name', 'id');
        $sectionMap = Section::whereIn('id', $sectionIds)->pluck('name', 'id');

        $items = $groups->getCollection()->map(function ($row) use ($classMap, $sectionMap) {
            return [
                'class_id'      => $row->class_id,
                'section_id'    => $row->section_id,
                'diary_date'    => $row->diary_date,
                'class_name'    => $classMap->get($row->class_id, '—'),
                'section_name'  => $sectionMap->get($row->section_id, '—'),
                'subject_count' => $row->subject_count,
                'created_at'    => $row->created_at,
            ];
        });

        return response()->json(new JsonResponse([
            'diaries' => array_merge($groups->toArray(), ['data' => $items]),
        ]));
    }

    /**
     * Get subjects assigned to a class (for the diary form).
     */
    public function getSubjectsByClass(Request $request)
    {
        $classId = $request->get('class_id');
        if (!$classId) {
            return response()->json(new JsonResponse(['subjects' => []]));
        }

        $class    = Classes::with('subjects')->find($classId);
        $subjects = $class ? $class->subjects : [];

        return response()->json(new JsonResponse(['subjects' => $subjects]));
    }

    /**
     * Get diary entries for a class + section + date (pre-fill form for edit).
     * Returns all subjects merged with existing diary text.
     */
    public function getDiaryByDate(Request $request)
    {
        $classId   = $request->get('class_id');
        $sectionId = $request->get('section_id');
        $date      = $request->get('diary_date') ? Carbon::parse($request->get('diary_date'))->format('Y-m-d') : null;

        if (!$classId || !$date) {
            return response()->json(new JsonResponse(['diaries' => []]));
        }

        $class    = Classes::with('subjects')->find($classId);
        $subjects = $class ? $class->subjects : collect();

        $existing = SchoolDiary::where('class_id', $classId)
            ->where('section_id', $sectionId ?: 0)
            ->whereDate('diary_date', $date)
            ->get()
            ->keyBy('subject_id');

        $diaries = $subjects->map(function ($subject) use ($existing) {
            $entry = $existing->get($subject->id);
            return [
                'subject_id'    => $subject->id,
                'subject_title' => $subject->title,
                'diary_id'      => $entry ? $entry->id : null,
                'diary_text'    => $entry ? $entry->diary_text : '',
            ];
        });

        return response()->json(new JsonResponse(['diaries' => $diaries]));
    }

    /**
     * Save (insert/update) diary entries for a class + section + date.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id'             => 'required|exists:classes,id',
            'section_id'           => 'nullable|integer',
            'diary_date'           => 'required|date',
            'entries'              => 'required|array|min:1',
            'entries.*.subject_id' => 'required|exists:subjects,id',
            'entries.*.diary_text' => 'nullable|string',
        ]);

        $classId   = $request->class_id;
        $sectionId = $request->section_id;
        $date      = Carbon::parse($request->diary_date)->format('Y-m-d');
        $entries   = $request->entries;
        $createdBy = auth()->id();

        DB::beginTransaction();
        try {
            foreach ($entries as $entry) {
                SchoolDiary::updateOrCreate(
                    [
                        'class_id'   => $classId,
                        'section_id' => $sectionId,
                        'subject_id' => $entry['subject_id'],
                        'diary_date' => $date,
                    ],
                    [
                        'diary_text' => $entry['diary_text'] ?? '',
                        'created_by' => $createdBy,
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(new JsonResponse([], $e->getMessage()), 500);
        }

        // Return the saved group record so the frontend can prepend it to the list
        $class   = Classes::find($classId);
        $section = Section::find($sectionId);
        $count   = SchoolDiary::where('class_id', $classId)
                        ->where('section_id', $sectionId)
                        ->whereDate('diary_date', $date)
                        ->where('diary_text', '!=', '')
                        ->count();

        return response()->json(new JsonResponse([
            'message' => 'Diary saved successfully',
            'group'   => [
                'class_id'      => $classId,
                'section_id'    => $sectionId,
                'diary_date'    => $date,
                'class_name'    => $class ? $class->name : '',
                'section_name'  => $section ? $section->name : '',
                'subject_count' => $count,
                'created_at'    => now()->toDateTimeString(),
            ],
        ]));
    }

    /**
     * Delete all diary entries for a class + section + date group.
     */
    public function deleteGroup(Request $request)
    {
        $request->validate([
            'class_id'   => 'required',
            'section_id' => 'required',
            'diary_date' => 'required|date',
        ]);

        SchoolDiary::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->whereDate('diary_date', $request->diary_date)
            ->delete();

        return response()->json(new JsonResponse(['message' => 'Diary deleted successfully']));
    }

    /**
     * Student/Parent view: diary for a class + section + date.
     */
    public function studentView(Request $request)
    {
        $classId   = $request->get('class_id');
        $sectionId = $request->get('section_id');
        $date      = $request->get('diary_date');

        if (!$classId || !$date) {
            return response()->json(new JsonResponse(['diaries' => []]));
        }

        $diaries = SchoolDiary::with('subject')
            ->where('class_id', $classId)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->whereDate('diary_date', $date)
            ->whereNotNull('diary_text')
            ->where('diary_text', '!=', '')
            ->get()
            ->map(function ($d) {
                return [
                    'subject_id'    => $d->subject_id,
                    'subject_title' => $d->subject ? $d->subject->title : '',
                    'diary_text'    => $d->diary_text,
                    'diary_date'    => $d->diary_date->format('Y-m-d'),
                ];
            });

        return response()->json(new JsonResponse(['diaries' => $diaries]));
    }

    /**
     * Distinct dates with diary entries for a class+section (for student navigation).
     */
    public function getDiaryDates(Request $request)
    {
        $classId   = $request->get('class_id');
        $sectionId = $request->get('section_id');

        if (!$classId || !$sectionId) {
            return response()->json(new JsonResponse(['dates' => []]));
        }

        $dates = SchoolDiary::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('diary_text', '!=', '')
            ->selectRaw('diary_date')
            ->distinct()
            ->orderByDesc('diary_date')
            ->limit(90)
            ->pluck('diary_date');

        return response()->json(new JsonResponse(['dates' => $dates]));
    }
}
