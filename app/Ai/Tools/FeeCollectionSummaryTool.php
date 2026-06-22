<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\SchoolQueryHelper;
use App\Models\FeeVoucher;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class FeeCollectionSummaryTool implements Tool
{
    use SchoolQueryHelper;

    public function __construct(public ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'Get a fee collection summary for a date range (by voucher generation date): total vouchers, paid/outstanding counts, amount generated, amount collected, and pending amount. Defaults to the current month.';
    }

    public function handle(Request $request): Stringable|string
    {
        $args = $request->all();
        $from = $this->parseDate($args['from'] ?? null, now()->startOfMonth());
        $to = $this->parseDate($args['to'] ?? null, now())->endOfDay();

        $stats = FeeVoucher::query()
            ->whereBetween('generated_at', [$from, $to])
            ->selectRaw("
                count(*) as total,
                sum(case when status = 'paid' then 1 else 0 end) as paid,
                sum(case when status in ('unpaid', 'partially_paid') then 1 else 0 end) as outstanding,
                coalesce(sum(total_with_fine), 0) as generated,
                coalesce(sum(paid_amount), 0) as collected,
                coalesce(sum(case when status in ('unpaid', 'partially_paid') then total_with_fine - coalesce(paid_amount, 0) else 0 end), 0) as pending
            ")
            ->first();

        return json_encode([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total_vouchers' => (int) $stats->total,
            'paid' => (int) $stats->paid,
            'outstanding' => (int) $stats->outstanding,
            'total_generated' => round((float) $stats->generated, 2),
            'total_collected' => round((float) $stats->collected, 2),
            'pending_amount' => round((float) $stats->pending, 2),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'from' => $schema->string()->description('Start date YYYY-MM-DD (default: start of current month).'),
            'to' => $schema->string()->description('End date YYYY-MM-DD (default: today).'),
        ];
    }

    public function allowedRoles(): array
    {
        return ['admin', 'manager'];
    }
}