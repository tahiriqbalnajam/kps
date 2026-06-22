<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\SchoolQueryHelper;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class AbsentStudentsTool implements Tool
{
    use SchoolQueryHelper;

    public function __construct(public ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'List students who were absent or on leave in a date range (optionally filtered by class/section). Includes date, status, and comment.';
    }

    public function handle(Request $request): Stringable|string
    {
        $args = $request->all();
        $from = $this->parseDate($args['from'] ?? null, now()->startOfDay());
        $to = $this->parseDate($args['to'] ?? null, $from->copy()->endOfDay());

        $classId = $this->resolveClassId($args['class'] ?? null);
        $sectionId = $this->resolveSectionId($args['section'] ?? null, $classId);

        $builder = StudentAttendance::with('students:id,name,roll_no,adminssion_number,class_id,section_id')
            ->where('status', '!=', 'present')
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()]);

        if ($classId) {
            $builder->where('class_id', $classId);
        }

        $records = $builder
            ->orderBy('attendance_date', 'desc')
            ->limit(300)
            ->get()
            ->map(function (StudentAttendance $a) {
                $s = $a->students;

                return [
                    'date' => (string) ($a->attendance_date ?? ''),
                    'student' => $s?->name,
                    'roll_no' => $s?->roll_no,
                    'status' => $a->status,
                    'comment' => $a->comment,
                ];
            });

        return json_encode([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'count' => $records->count(),
            'records' => $records,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'from' => $schema->string()->description('Start date YYYY-MM-DD.')->required(),
            'to' => $schema->string()->description('End date YYYY-MM-DD (default: same as from).'),
            'class' => $schema->string()->description('Optional class name.'),
            'section' => $schema->string()->description('Optional section name.'),
        ];
    }

    public function allowedRoles(): array
    {
        return [];
    }
}