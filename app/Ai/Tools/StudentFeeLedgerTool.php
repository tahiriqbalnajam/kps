<?php

namespace App\Ai\Tools;

use App\Models\FeeVoucher;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class StudentFeeLedgerTool implements Tool
{
    public function __construct(public ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'Get a student\'s fee ledger: total charged, total paid, balance, unpaid voucher count, and recent vouchers.';
    }

    public function handle(Request $request): Stringable|string
    {
        $id = $request->integer('student_id');

        $vouchers = FeeVoucher::where('student_id', $id)
            ->orderBy('due_date', 'desc')
            ->limit(50)
            ->get();

        if ($vouchers->isEmpty()) {
            return json_encode(['error' => 'No vouchers found', 'student_id' => $id]);
        }

        $charged = $vouchers->sum(fn (FeeVoucher $v) => (float) $v->total_with_fine);
        $paid = $vouchers->sum(fn (FeeVoucher $v) => (float) $v->paid_amount);
        $unpaid = $vouchers->whereIn('status', ['unpaid', 'partially_paid'])->count();

        $rows = $vouchers->map(fn (FeeVoucher $v) => [
            'voucher_number' => $v->voucher_number,
            'fee_month' => $v->fee_month,
            'fee_amount' => (float) $v->fee_amount,
            'paid' => (float) $v->paid_amount,
            'remaining' => $v->remaining_amount,
            'due_date' => (string) ($v->due_date ?? ''),
            'status' => $v->status,
        ]);

        return json_encode([
            'student_id' => $id,
            'total_charged' => round($charged, 2),
            'total_paid' => round($paid, 2),
            'balance' => round($charged - $paid, 2),
            'unpaid_vouchers' => $unpaid,
            'recent_vouchers' => $rows,
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
        return ['admin', 'manager'];
    }
}