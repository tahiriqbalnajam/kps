<?php

namespace App\Ai\Agents;

use App\Ai\Tools\AbsentStudentsTool;
use App\Ai\Tools\ClassAttendanceSummaryTool;
use App\Ai\Tools\FeeCollectionSummaryTool;
use App\Ai\Tools\GetStudentProfileTool;
use App\Ai\Tools\ListStudentsByClassTool;
use App\Ai\Tools\SearchStudentsTool;
use App\Ai\Tools\StudentAttendanceSummaryTool;
use App\Ai\Tools\StudentFeeLedgerTool;
use App\Ai\Tools\UnpaidVouchersTool;
use App\Models\Acl;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxSteps(12)]
#[MaxTokens(4096)]
#[Temperature(0.2)]
#[Timeout(180)]
class DataQueryAgent implements Agent, HasTools, HasStructuredOutput
{
    use Promptable;

    public function __construct(public ?User $user = null) {}

    public function instructions(): Stringable|string
    {
        return <<<'TEXT'
You are the data assistant for a school management system (students, attendance, and fees).

Rules:
- You MUST call the available tools to retrieve any factual data (counts, names, amounts, dates). Never invent or estimate numbers — if a tool did not return it, do not state it.
- The data is already scoped to the current school/tenant; just call the tools.
- Currency is PKR (Pakistani Rupees). Dates are YYYY-MM-DD.
- To answer a question, choose the most specific tool. If you need a student's ID, first call search_students or list_students_by_class.
- After gathering data, respond with ONLY a single valid JSON object — no markdown, no code fences, no prose before or after — with this exact shape:
  {"summary": string, "highlights": string[], "table": {"columns": string[], "rows": array}, "chart": {"type": string, "data": array} | null, "sources": string[]}
  - summary: a concise, plain-language answer to the user's question.
  - highlights: a few short bullet points (key numbers/facts), may be empty [].
  - table: when the answer has rows of records, provide {"columns": [...], "rows": [...]}; rows may be arrays aligned to columns or objects keyed by column name. If no table applies, use {"columns": [], "rows": []}.
  - chart: optional; when a comparison/trend is useful, provide {"type": "bar"|"pie"|"line", "data": [...]}; otherwise null.
  - sources: the names of the tools you actually called, may be empty [].
- If a tool returns an error or a "not found" result, state that clearly and factually in `summary` (e.g. which class/student/record was not found) and still return valid JSON with empty rows and the tool in sources. Never respond with a greeting or ask what the user wants — always answer using the tool results.
- If no tool applies or the request is out of scope, explain the limitation in `summary` and return {"columns": [], "rows": []} for table, null for chart, and [] for sources.
TEXT;
    }

    public function tools(): iterable
    {
        $tools = [
            new SearchStudentsTool($this->user),
            new GetStudentProfileTool($this->user),
            new ListStudentsByClassTool($this->user),
            new ClassAttendanceSummaryTool($this->user),
            new AbsentStudentsTool($this->user),
            new StudentAttendanceSummaryTool($this->user),
            new UnpaidVouchersTool($this->user),
            new FeeCollectionSummaryTool($this->user),
            new StudentFeeLedgerTool($this->user),
        ];

        $roles = $this->user?->roles?->pluck('name')->all() ?? [];

        return array_values(array_filter(
            $tools,
            fn (Tool $tool) => $this->toolAllowedFor($tool, $roles)
        ));
    }

    /**
     * A tool is allowed if the user is an admin, or their roles intersect
     * the tool's allowedRoles (empty = any authenticated role).
     */
    protected function toolAllowedFor(Tool $tool, array $roles): bool
    {
        if (in_array(Acl::ROLE_ADMIN, $roles, true)) {
            return true;
        }

        if (! method_exists($tool, 'allowedRoles')) {
            return true;
        }

        $allowed = $tool->allowedRoles();

        return empty($allowed) || (bool) array_intersect($allowed, $roles);
    }

    /**
     * The model to use (configurable via AI_MODEL, defaults to glm-5.2 on Ollama Cloud).
     */
    public function model(): string
    {
        return (string) config('ai.data_agent.model', 'glm-5.2');
    }

    /**
     * Structured output schema. With the DeepSeek driver (used for Ollama Cloud, which
     * does not support json_schema), this is translated to response_format json_object
     * plus the schema described in the prompt — forcing valid JSON without requiring
     * Cloud's unsupported structured outputs.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()->description('Concise natural-language answer.')->required(),
            'highlights' => $schema->array()->items($schema->string())->description('Key bullet points.'),
            'table' => $schema->object([
                'columns' => $schema->array()->items($schema->string()),
                'rows' => $schema->array()->description('Array of row records.'),
            ])->description('Tabular data when relevant.'),
            'chart' => $schema->object([
                'type' => $schema->string()->description('bar, pie, or line.'),
                'data' => $schema->array()->description('Chart-ready data.'),
            ])->nullable()->description('Optional chart specification.'),
            'sources' => $schema->array()->items($schema->string())->description('Names of tools used.'),
        ];
    }
}