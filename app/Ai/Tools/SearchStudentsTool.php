<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\SchoolQueryHelper;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchStudentsTool implements Tool
{
    use SchoolQueryHelper;

    public function __construct(public ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'Search students by name, roll number, admission number, or B-form. Returns a list with class and section. Use this to find a student before calling profile/attendance/fee tools.';
    }

    public function handle(Request $request): Stringable|string
    {
        $args = $request->all();
        $query = (string) $request->string('query');
        $classId = $this->resolveClassId($args['class'] ?? null);
        $sectionId = $this->resolveSectionId($args['section'] ?? null, $classId);
        $limit = $request->integer('limit') ?: 25;

        $builder = Student::query()->where('status', 'enable');

        if ($query !== '') {
            $builder->where(function ($b) use ($query) {
                $b->where('name', 'like', "%{$query}%")
                    ->orWhere('roll_no', 'like', "%{$query}%")
                    ->orWhere('adminssion_number', 'like', "%{$query}%")
                    ->orWhere('b_form', 'like', "%{$query}%");
            });
        }

        if ($classId) {
            $builder->where('class_id', $classId);
        }

        if ($sectionId) {
            $builder->where('section_id', $sectionId);
        }

        $students = $builder->with(['stdclasses' => fn ($q) => $q->withoutGlobalScope('priority'), 'section'])
            ->orderBy('name')
            ->limit(min($limit, 100))
            ->get()
            ->map(fn (Student $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'roll_no' => $s->roll_no,
                'admission_number' => $s->adminssion_number,
                'class' => $s->stdclasses?->name,
                'section' => $s->section?->name,
                'status' => $s->status,
            ]);

        return json_encode(['count' => $students->count(), 'students' => $students]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Search term: name, roll number, admission number, or B-form.')->required(),
            'class' => $schema->string()->description('Optional class name to filter by.'),
            'section' => $schema->string()->description('Optional section name to filter by.'),
            'limit' => $schema->integer()->description('Max results (default 25).')->min(1)->max(100),
        ];
    }

    /**
     * Roles permitted to use this tool. Empty = any authenticated role.
     */
    public function allowedRoles(): array
    {
        return [];
    }
}