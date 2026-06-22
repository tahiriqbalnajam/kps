<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\SchoolQueryHelper;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListStudentsByClassTool implements Tool
{
    use SchoolQueryHelper;

    public function __construct(public ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'List active students in a given class (and optionally a section). Returns the count and the student list. Use a class name like "5" or "Class 5".';
    }

    public function handle(Request $request): Stringable|string
    {
        $args = $request->all();
        $classId = $this->resolveClassId($args['class'] ?? null);

        if (! $classId) {
            return json_encode(['error' => 'Class not found', 'class' => $args['class'] ?? null]);
        }

        $sectionId = $this->resolveSectionId($args['section'] ?? null, $classId);

        $builder = Student::query()->where('status', 'enable')->where('class_id', $classId);

        if ($sectionId) {
            $builder->where('section_id', $sectionId);
        }

        $students = $builder->with('section')
            ->orderBy('roll_no')
            ->limit(300)
            ->get()
            ->map(fn (Student $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'roll_no' => $s->roll_no,
                'section' => $s->section?->name,
            ]);

        return json_encode([
            'class' => $args['class'] ?? null,
            'section' => $args['section'] ?? null,
            'count' => $students->count(),
            'students' => $students,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'class' => $schema->string()->description('Class name, e.g. "5" or "Class 5".')->required(),
            'section' => $schema->string()->description('Optional section name.'),
        ];
    }

    public function allowedRoles(): array
    {
        return [];
    }
}