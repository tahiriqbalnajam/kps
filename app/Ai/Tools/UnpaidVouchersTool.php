<?php

namespace App\Ai\Tools;

use App\Models\FeeVoucher;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class UnpaidVouchersTool implements Tool
{
    public function __construct(public ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'List unpaid and partially-paid fee vouchers, optionally filtered by student IDs, class, due-date range, or overdue-only. Returns remaining amounts and total outstanding.';
    }

    public function handle(Request $request): Stringable|string
    {
        $args = $request->all();

        $builder = FeeVoucher::query()->whereIn('status', ['unpaid', 'partially_paid']);

        if ($request->boolean('overdue_only')) {
            $builder->where('due_date', '<', now()->toDateString());
        }

        $studentIds = $args['student_ids'] ?? [];

        if (! empty($studentIds)) {
            $builder->whereIn('student_id', array_map('intval', (array) $studentIds));
        }

        if (! empty($args['class'])) {
            $builder->byClass($args['class']);
        }

        if (! empty($args['from']) && ! empty($args['to'])) {
            $builder->whereBetween('due_date', [$args['from'], $args['to']]);
        }

        $vouchers = $builder->orderBy('due_date')->limit(100)->get()
            ->map(fn (FeeVoucher $v) => [
                'voucher_number' => $v->voucher_number,
                'student' => $v->student_name,
                'class' => $v->class_name,
                'fee_amount' => (float) $v->fee_amount,
                'fine' => (float) $v->fine_amount,
                'remaining' => $v->remaining_amount,
                'due_date' => (string) ($v->due_date ?? ''),
                'status' => $v->status,
            ]);

        $totalOutstanding = $vouchers->sum('remaining');

        return json_encode([
            'count' => $vouchers->count(),
            'total_outstanding' => round($totalOutstanding, 2),
            'vouchers' => $vouchers,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'student_ids' => $schema->array()->items($schema->integer())->description('Optional list of student IDs.'),
            'class' => $schema->string()->description('Optional class name.'),
            'from' => $schema->string()->description('Optional due-date start YYYY-MM-DD.'),
            'to' => $schema->string()->description('Optional due-date end YYYY-MM-DD.'),
            'overdue_only' => $schema->boolean()->description('Only vouchers past their due date.'),
        ];
    }

    public function allowedRoles(): array
    {
        return ['admin', 'manager'];
    }
}