<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeVoucher;
use Illuminate\Http\Request;

class MobileFeeController extends Controller
{
    /**
     * GET /v1/fee/vouchers?student_id=X
     * Returns all vouchers for a specific student.
     */
    public function getStudentVouchers(Request $request)
    {
        $studentId = $request->query('student_id');

        if (!$studentId) {
            return response()->json([
                'success' => false,
                'message' => 'student_id is required.',
            ], 422);
        }

        $vouchers = FeeVoucher::where('student_id', $studentId)
            ->orderBy('generated_at', 'desc')
            ->get()
            ->map(function ($v) {
                return [
                    'id'               => $v->id,
                    'voucher_number'   => $v->voucher_number,
                    'month'            => $v->fee_month,
                    'total_amount'     => (float) $v->total_with_fine,
                    'paid_amount'      => (float) ($v->paid_amount ?? 0),
                    'remaining_amount' => (float) $v->remaining_amount,
                    'status'           => $v->status === 'unpaid' ? 'pending' : $v->status,
                    'due_date'         => $v->due_date?->toDateString(),
                    'issue_date'       => $v->generated_at?->toDateString(),
                    'details'          => $this->mapBreakdown($v->fee_breakdown),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $vouchers,
        ]);
    }

    /**
     * GET /v1/fee/summary?student_id=X
     * Returns outstanding, paid, and pending totals for a student.
     */
    public function getStudentSummary(Request $request)
    {
        $studentId = $request->query('student_id');

        if (!$studentId) {
            return response()->json([
                'success' => false,
                'message' => 'student_id is required.',
            ], 422);
        }

        $vouchers = FeeVoucher::where('student_id', $studentId)->get();

        $totalOutstanding = $vouchers
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->sum('remaining_amount');

        $totalPaid = $vouchers
            ->where('status', 'paid')
            ->sum('paid_amount');

        $totalPending = $vouchers
            ->where('status', 'unpaid')
            ->sum('total_with_fine');

        return response()->json([
            'success' => true,
            'data'    => [
                'total_outstanding' => round((float) $totalOutstanding, 2),
                'total_paid'        => round((float) $totalPaid, 2),
                'total_pending'     => round((float) $totalPending, 2),
            ],
        ]);
    }

    private function mapBreakdown($breakdown): array
    {
        if (empty($breakdown) || !is_array($breakdown)) {
            return [];
        }

        return collect($breakdown)->map(function ($item, $index) {
            return [
                'id'          => $index + 1,
                'voucher_id'  => 0,
                'fee_type'    => $item['fee_type'] ?? $item['name'] ?? 'Fee',
                'amount'      => (float) ($item['amount'] ?? 0),
                'description' => $item['description'] ?? null,
            ];
        })->values()->all();
    }
}
