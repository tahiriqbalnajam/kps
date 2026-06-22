<?php

namespace App\Ai\Tools;

use App\Models\FeeVoucher;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetStudentProfileTool implements Tool
{
    public function __construct(public ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'Get a single student\'s full profile: guardian, class, section, B-form/NADRA status, monthly fee, and a fee summary (paid/outstanding/balance).';
    }

    public function handle(Request $request): Stringable|string
    {
        $id = $request->integer('student_id');

        $student = Student::with([
            'parents',
            'stdclasses' => fn ($q) => $q->withoutGlobalScope('priority'),
            'section',
        ])->find($id);

        if (! $student) {
            return json_encode(['error' => 'Student not found', 'student_id' => $id]);
        }

        $fee = FeeVoucher::where('student_id', $id)
            ->selectRaw("
                count(*) as total,
                sum(case when status = 'paid' then 1 else 0 end) as paid,
                sum(case when status in ('unpaid', 'partially_paid') then 1 else 0 end) as outstanding,
                coalesce(sum(case when status in ('unpaid', 'partially_paid') then total_with_fine - coalesce(paid_amount, 0) else 0 end), 0) as balance
            ")
            ->first();

        return json_encode([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'roll_no' => $student->roll_no,
                'admission_number' => $student->adminssion_number,
                'gender' => $student->gender,
                'dob' => $student->dob,
                'class' => $student->stdclasses?->name,
                'section' => $student->section?->name,
                'b_form' => $student->b_form,
                'nadra_pending' => $student->nadra_pending,
                'monthly_fee' => $student->monthly_fee,
                'status' => $student->status,
            ],
            'guardian' => $student->parents ? [
                'name' => $student->parents->name,
                'phone' => $student->parents->phone ?? null,
            ] : null,
            'fee' => [
                'total_vouchers' => (int) $fee->total,
                'paid' => (int) $fee->paid,
                'outstanding' => (int) $fee->outstanding,
                'balance' => round((float) $fee->balance, 2),
            ],
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