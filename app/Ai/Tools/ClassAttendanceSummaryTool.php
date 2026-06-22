<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\SchoolQueryHelper;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ClassAttendanceSummaryTool implements Tool
{
    use SchoolQueryHelper;

    public function __construct(public ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'Get an attendance summary for a class over a date range: present/absent/leave counts and percentages. Defaults to the last 7 days.';
    }

    public function handle(Request $request): Stringable|string
    {
        $args = $request->all();
        $classId = $this->resolveClassId($args['class'] ?? null);

        if (! $classId) {
            return json_encode(['error' => 'Class not found', 'class' => $args['class'] ?? null]);
        }

        $from = $this->parseDate($args['from'] ?? null, now()->subDays(6)->startOfDay());
        $to = $this->parseDate($args['to'] ?? null, now());

        $counts = StudentAttendance::where('class_id', $classId)
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $present = (int) ($counts['present'] ?? 0);
        $absent = (int) ($counts['absent'] ?? 0);
        $leave = (int) ($counts['leave'] ?? 0);
        $total = $present + $absent + $leave;

        return json_encode([
            'class' => $args['class'] ?? null,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'present' => $present,
            'absent' => $absent,
            'leave' => $leave,
            'total' => $total,
            'present_pct' => $total ? round($present * 100 / $total, 1) : 0,
            'absent_pct' => $total ? round($absent * 100 / $total, 1) : 0,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'class' => $schema->string()->description('Class name.')->required(),
            'from' => $schema->string()->description('Start date YYYY-MM-DD (default: 7 days ago).'),
            'to' => $schema->string()->description('End date YYYY-MM-DD (default: today).'),
        ];
    }

    public function allowedRoles(): array
    {
        return [];
    }
}