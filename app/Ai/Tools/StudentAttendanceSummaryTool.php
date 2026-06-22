<?php

namespace App\Ai\Tools;

use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class StudentAttendanceSummaryTool implements Tool
{
    public function __construct(public ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'Get a single student\'s overall attendance summary: present/absent/leave counts, percentage, and today\'s status.';
    }

    public function handle(Request $request): Stringable|string
    {
        $id = $request->integer('student_id');

        if (! Student::whereKey($id)->exists()) {
            return json_encode(['error' => 'Student not found', 'student_id' => $id]);
        }

        $counts = StudentAttendance::where('student_id', $id)
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $present = (int) ($counts['present'] ?? 0);
        $absent = (int) ($counts['absent'] ?? 0);
        $leave = (int) ($counts['leave'] ?? 0);
        $total = $present + $absent + $leave;

        $today = StudentAttendance::where('student_id', $id)
            ->where('attendance_date', now()->toDateString())
            ->first();

        return json_encode([
            'student_id' => $id,
            'present' => $present,
            'absent' => $absent,
            'leave' => $leave,
            'total' => $total,
            'present_pct' => $total ? round($present * 100 / $total, 1) : 0,
            'today_status' => $today?->status ?? 'no_record',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'student_id' => $schema->integer()->description('The student ID.')->required(),
        ];
    }

    public function allowedRoles(): array
    {
        return [];
    }
}