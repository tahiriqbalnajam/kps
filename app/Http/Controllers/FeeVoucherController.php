<?php

namespace App\Http\Controllers;

use App\Models\FeeVoucher;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Models\Settings;
use Carbon\Carbon;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class FeeVoucherController extends Controller
{
    /**
     * Get students eligible for fee vouchers
     */
    public function getStudents(Request $request)
    {
        try {
            $query = Student::with(['stdclasses', 'parents']);
            
            // Apply filters
            if ($request->has('filter')) {
                $filters = $request->input('filter');
                
                // Specific filters
                if (!empty($filters['student_name'])) {
                    $query->where('name', 'LIKE', "{$filters['student_name']}%");
                }
                if (!empty($filters['parent_name'])) {
                    $val = $filters['parent_name'];
                    $query->whereHas('parents', function($q) use ($val) {
                         $q->where('name', 'LIKE', "{$val}%");
                    });
                }
                if (!empty($filters['admission_number'])) {
                    $query->where('adminssion_number', 'LIKE', "{$filters['admission_number']}%");
                }
                if (!empty($filters['roll_no'])) {
                    $query->where('roll_no', 'LIKE', "{$filters['roll_no']}%");
                }

                // Generic search
                if (isset($filters['search']) && !empty($filters['search'])) {
                    $searchTerm = $filters['search'];
                    $query->where(function ($q) use ($searchTerm) {
                        $q->where('name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('adminssion_number', 'LIKE', "%{$searchTerm}%")
                          ->orWhereHas('parents', function ($pq) use ($searchTerm) {
                              $pq->where('name', 'LIKE', "%{$searchTerm}%");
                          });
                    });
                }
                
                if (isset($filters['stdclass'])) {
                    $query->where('class_id', $filters['stdclass']);
                }
                
                if (isset($filters['gender'])) {
                    $query->where('gender', $filters['gender']);
                }
                
                if (isset($filters['status'])) {
                    if ($filters['status'] === 'active') {
                        $query->where(function($q) { 
                            $q->where('status', 'active')->orWhere('status', 1); 
                        });
                    } else {
                        $query->where('status', $filters['status']);
                    }
                }
            }
            
            // Pagination
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 15);
            
            $students = $query->paginate($limit, ['*'], 'page', $page);
            
            return response()->json([
                'success' => true,
                'students' => $students
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and save fee vouchers
     */
    public function generateVouchers(Request $request)
    {
        try {
            $request->validate([
                'vouchers' => 'required|array',
                'vouchers.*.student_id' => 'required|integer',
                'vouchers.*.student_name' => 'required|string',
                'vouchers.*.admission_number' => 'required|string',
                'vouchers.*.parent_name' => 'required|string',
                'vouchers.*.class_name' => 'required|string',
                'vouchers.*.fee_amount' => 'required|numeric|min:0',
                'due_date' => 'required|date',
                'fine_amount' => 'required|numeric|min:0',
                'voucher_type' => 'required|in:monthly,custom,multiple',
                'custom_amount' => 'nullable|numeric|min:0',
                'fee_month' => 'nullable|date_format:Y-m',
                'selected_fee_types' => 'nullable|array',
                'notes' => 'nullable|string|max:500'
            ]);

            // Check for existing vouchers before generating new ones
            $duplicates = [];
            foreach ($request->vouchers as $voucherData) {
                $existingQuery = FeeVoucher::where('student_id', $voucherData['student_id'])
                                         ->where('status', '!=', 'cancelled');
                
                if ($request->voucher_type === 'monthly' && $request->fee_month) {
                    $existingQuery->where('voucher_type', 'monthly')
                                 ->where('fee_month', $request->fee_month);
                } else if ($request->voucher_type === 'custom') {
                    $existingQuery->where('voucher_type', 'custom')
                                 ->where('due_date', $request->due_date);
                } else if ($request->voucher_type === 'multiple' && $request->selected_fee_types) {
                    $existingQuery->where('voucher_type', 'multiple')
                                 ->where('fee_month', $request->fee_month);
                }
                
                $existing = $existingQuery->first();
                if ($existing) {
                    $duplicates[] = [
                        'student_name' => $voucherData['student_name'],
                        'admission_number' => $voucherData['admission_number'],
                        'existing_voucher' => $existing->voucher_number,
                        'type' => $request->voucher_type,
                        'month' => $request->fee_month
                    ];
                }
            }
            
            if (!empty($duplicates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate vouchers found for some students',
                    'duplicates' => $duplicates
                ], 422);
            }

            $savedVouchers = [];
            $maxRetries = 3;
            $voucherCreated = false;
            $datePrefix = 'IDL-' . now()->format('ymd');

            for ($retry = 0; $retry < $maxRetries && !$voucherCreated; $retry++) {
                try {
                    DB::beginTransaction();

                    // Lock the latest voucher for today to prevent race conditions
                    $lastVoucher = FeeVoucher::where('voucher_number', 'like', $datePrefix . '-%')
                        ->orderBy('voucher_number', 'desc')
                        ->lockForUpdate()
                        ->value('voucher_number');

                    $lastSeq = 0;
                    if ($lastVoucher && preg_match('/-(\d{3})$/', $lastVoucher, $m)) {
                        $lastSeq = (int) $m[1];
                    }

                    foreach ($request->vouchers as $index => $voucherData) {
                        $voucher = FeeVoucher::create([
                            'voucher_number' => $datePrefix . '-' . str_pad($lastSeq + $index + 1, 3, '0', STR_PAD_LEFT),
                    'student_id' => $voucherData['student_id'],
                    'student_name' => $voucherData['student_name'],
                    'admission_number' => $voucherData['admission_number'],
                    'parent_name' => $voucherData['parent_name'],
                    'parent_phone' => $voucherData['parent_phone'] ?? null,
                    'parent_email' => $voucherData['parent_email'] ?? null,
                    'class_name' => $voucherData['class_name'],
                    'fee_amount' => (float) $voucherData['fee_amount'],
                    'fine_amount' => (float) $request->fine_amount,
                    'total_with_fine' => (float) $voucherData['fee_amount'] + (float) $request->fine_amount,
                    'due_date' => $request->due_date,
                    'voucher_type' => $request->voucher_type,
                    'custom_amount' => $request->custom_amount ? (float) $request->custom_amount : null,
                    'fee_month' => $request->fee_month,
                    'fee_breakdown' => $voucherData['fee_breakdown'] ?? null,
                    'selected_fee_types' => $request->selected_fee_types,
                    'notes' => $request->notes,
                    'status' => 'unpaid',
                    'generated_by' => Auth::id() ?? 1,
                    'generated_at' => now()
                ]);
                
                $savedVouchers[] = $voucher;
            }
            
                    DB::commit();
                    $voucherCreated = true;

                    return response()->json([
                        'success' => true,
                        'message' => count($savedVouchers) . ' vouchers generated successfully',
                        'saved_vouchers' => $savedVouchers
                    ]);

                } catch (\Illuminate\Database\QueryException $e) {
                    DB::rollBack();
                    // Retry only on duplicate key errors
                    if ($e->getCode() != 23000 || $retry >= $maxRetries - 1) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to generate vouchers: ' . $e->getMessage()
                        ], 500);
                    }
                    // Otherwise continue to retry with a fresh sequence lookup
                    $savedVouchers = [];
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate vouchers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of generated vouchers (Spatie QueryBuilder)
     */
    public function getVouchers(Request $request)
    {
        try {
            $limit = $request->input('limit', 15);

            $vouchers = QueryBuilder::for(FeeVoucher::class)
                ->allowedFilters(...[
                AllowedFilter::exact('status'),
                AllowedFilter::partial('class_name'), // Keeping partial for class as it's often short
                AllowedFilter::exact('voucher_type'),
                AllowedFilter::callback('date_from', function ($query, $value) {
                    $query->whereDate('generated_at', '>=', $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value) {
                    $query->whereDate('generated_at', '<=', $value);
                }),
                AllowedFilter::callback('paid_from', function ($query, $value) {
                    $query->whereDate('payment_date', '>=', $value);
                }),
                AllowedFilter::callback('paid_to', function ($query, $value) {
                    $query->whereDate('payment_date', '<=', $value);
                }),
                // Name search using FULLTEXT index (index-backed, word-level matching)
                AllowedFilter::callback('student_name', function ($query, $value) {
                    $query->whereRaw(
                        "MATCH(student_name) AGAINST(? IN BOOLEAN MODE)",
                        ['+' . $value . '*']
                    );
                }),
                AllowedFilter::callback('name', function ($query, $value) {
                    $query->whereRaw(
                        "MATCH(student_name) AGAINST(? IN BOOLEAN MODE)",
                        ['+' . $value . '*']
                    );
                }),
                AllowedFilter::callback('parent_name', function ($query, $value) {
                    $query->whereRaw(
                        "MATCH(parent_name) AGAINST(? IN BOOLEAN MODE)",
                        ['+' . $value . '*']
                    );
                }),
                AllowedFilter::callback('voucher_number', function ($query, $value) {
                    $query->where('voucher_number', 'LIKE', "{$value}%");
                }),
                AllowedFilter::callback('roll_no', function ($query, $value) {
                    $query->whereHas('student', function ($q) use ($value) {
                        $q->where('roll_no', 'LIKE', "{$value}%");
                    });
                }),
                // Generic search: FULLTEXT for names, prefix LIKE for indexed identifiers
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->whereRaw(
                            "MATCH(student_name, parent_name) AGAINST(? IN BOOLEAN MODE)",
                            ['+' . $value . '*']
                        )
                          ->orWhere('voucher_number', 'LIKE', "{$value}%")
                          ->orWhere('admission_number', 'LIKE', "{$value}%")
                          ->orWhere('parent_phone', 'LIKE', "{$value}%");
                    });
                }),
                AllowedFilter::callback('overdue_only', function ($query, $value) {
                    if ($value) {
                        $query->whereIn('status', ['unpaid', 'partially_paid'])
                              ->where('due_date', '<', now()->toDateString());
                    }
                }),
            ])
                ->orderByRaw("
                    CASE 
                        WHEN status = 'unpaid' AND due_date < CURDATE() THEN 0
                        WHEN status = 'partially_paid' AND due_date < CURDATE() THEN 1
                        WHEN status = 'unpaid' THEN 2
                        WHEN status = 'partially_paid' THEN 3
                        WHEN status = 'paid' THEN 4
                        WHEN status = 'cancelled' THEN 5
                        ELSE 6
                    END ASC
                ")
                ->orderBy('due_date', 'asc')
                ->paginate($limit)
                ->appends(request()->query());

            return response()->json([
                'success' => true,
                'vouchers' => $vouchers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vouchers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific voucher details
     */
    public function getVoucherDetails($id)
    {
        try {
            $voucher = FeeVoucher::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'voucher' => $voucher
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update voucher status (paid/unpaid/partially_paid/cancelled)
     *
     * PAYMENT SEMANTICS:
     *   - `paid_amount`  in the REQUEST  = the NEW incremental payment being made right now.
     *   - `paid_amount`  on the DB row   = running cumulative total (auto-calculated here).
     *   - Status is auto-determined by comparing the new cumulative total to total_with_fine.
     *     The caller's `status` field is IGNORED for payment flows to prevent the frontend
     *     from accidentally forcing 'paid' on a partial amount.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status'              => 'required|in:paid,unpaid,partially_paid,cancelled',
                'paid_amount'         => 'nullable|numeric|min:0',
                'payment_date'        => 'nullable|date',
                'pending_voucher_ids' => 'nullable|array',
            ]);

            $voucher = FeeVoucher::findOrFail($id);

            DB::beginTransaction();

            try {
                // --- Cancellation: no payment logic needed ---
                if ($request->status === 'cancelled') {
                    $voucher->update([
                        'status'     => 'cancelled',
                        'updated_by' => Auth::id() ?? 1,
                    ]);
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'Voucher cancelled successfully',
                        'voucher' => $voucher->fresh(),
                    ]);
                }

                // Fine only applies after due date
                $dueDate     = $voucher->due_date;
                $today       = now()->toDateString();
                $fineApplies = $dueDate && $today > $dueDate;
                $targetAmount = round((float) ($fineApplies ? $voucher->total_with_fine : $voucher->fee_amount), 2);

                $alreadyPaid  = round((float) ($voucher->paid_amount ?? 0), 2);
                $newPayment   = round((float) ($request->paid_amount ?? 0), 2);
                $paymentDate  = $request->payment_date ?? now()->toDateString();

                if ($newPayment > 0) {
                    // --- Overpayment guard ---
                    $remaining = round($targetAmount - $alreadyPaid, 2);
                    if ($newPayment > $remaining + 0.005) {
                        DB::rollBack();
                        return response()->json([
                            'success'          => false,
                            'message'          => "Payment of Rs. {$newPayment} exceeds the remaining balance of Rs. {$remaining}.",
                            'remaining_amount' => $remaining,
                        ], 422);
                    }

                    // --- Accumulate ---
                    $newCumulativeTotal = round($alreadyPaid + $newPayment, 2);

                    // --- Auto-determine status (ignores the request `status` field) ---
                    if ($newCumulativeTotal >= $targetAmount - 0.005) {
                        $resolvedStatus     = 'paid';
                        $newCumulativeTotal = $targetAmount; // remove float dust
                    } else {
                        $resolvedStatus = 'partially_paid';
                    }

                    $voucher->update([
                        'status'       => $resolvedStatus,
                        'paid_amount'  => $newCumulativeTotal,
                        'payment_date' => $paymentDate,
                        'updated_by'   => Auth::id() ?? 1,
                    ]);

                } else {
                    // No payment amount provided — honour the explicit status as-is
                    // (handles resets like marking back to unpaid by admin)
                    $resolvedStatus = $request->status;
                    $voucher->update([
                        'status'     => $resolvedStatus,
                        'updated_by' => Auth::id() ?? 1,
                    ]);
                }

                // Bulk-mark additional pending vouchers (bundle payment feature)
                if ($resolvedStatus === 'paid'
                    && $request->filled('pending_voucher_ids')
                    && count($request->pending_voucher_ids) > 0
                ) {
                    FeeVoucher::whereIn('id', $request->pending_voucher_ids)
                        ->whereNotIn('status', ['paid', 'cancelled'])
                        ->update([
                            'status'       => 'paid',
                            'payment_date' => $paymentDate,
                            'updated_by'   => Auth::id() ?? 1,
                        ]);
                }

                DB::commit();

                $freshVoucher     = $voucher->fresh();
                $remainingAfter   = round($targetAmount - (float) ($freshVoucher->paid_amount ?? 0), 2);

                $message = match ($resolvedStatus) {
                    'paid'           => 'Voucher marked as fully paid',
                    'partially_paid' => "Partial payment of Rs. {$newPayment} recorded. Remaining: Rs. {$remainingAfter}",
                    default          => 'Voucher status updated successfully',
                };

                return response()->json([
                    'success'          => true,
                    'message'          => $message,
                    'voucher'          => $freshVoucher,
                    'remaining_amount' => $remainingAfter,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update voucher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete/Cancel voucher
     */
    public function deleteVoucher($id)
    {
        try {
            $voucher = FeeVoucher::findOrFail($id);
            
            // Only allow deletion of vouchers where no money has been received
            if (in_array($voucher->status, ['paid', 'partially_paid'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete paid or partially paid vouchers. Cancel instead.'
                ], 400);
            }
            
            $voucher->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Voucher deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete voucher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get outstanding/unpaid vouchers
     */
    public function getOutstandingVouchers(Request $request)
    {
        try {
            // Include both unpaid and partially_paid vouchers as outstanding
            $query = FeeVoucher::whereIn('status', ['unpaid', 'partially_paid']);

            // Filter by specific student IDs if provided
            if ($request->filled('student_ids')) {
                $studentIds = explode(',', $request->student_ids);
                $query->whereIn('student_id', $studentIds);
            }
            
            // Apply filters
            if ($request->filled('urgency')) {
                switch ($request->urgency) {
                    case 'overdue':
                        $query->where('due_date', '<', now()->toDateString());
                        break;
                    case 'due_soon':
                        $query->whereBetween('due_date', [now()->toDateString(), now()->addDays(3)->toDateString()]);
                        break;
                    case 'due_week':
                        $query->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()]);
                        break;
                }
            }
            
            if ($request->filled('class_name')) {
                $query->where('class_name', $request->class_name);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereRaw(
                        "MATCH(student_name, parent_name) AGAINST(? IN BOOLEAN MODE)",
                        ['+' . $search . '*']
                    )
                      ->orWhere('voucher_number', 'like', "{$search}%");
                });
            }
            
            if ($request->filled('due_from') && $request->filled('due_to')) {
                $query->whereBetween('due_date', [$request->due_from, $request->due_to]);
            }
            
            // Check for overdue vouchers (legacy support)
            if ($request->boolean('overdue_only')) {
                $query->where('due_date', '<', now()->toDateString());
            }
            
            $vouchers = $query->orderBy('due_date', 'asc')->get();
            
            // Add computed fields for frontend
            $vouchers = $vouchers->map(function($voucher) {
                $voucher->parent_phone = $voucher->parent_phone ?? '';
                $voucher->parent_email = $voucher->parent_email ?? '';
                
                // Fine only applies after due date
                $isOverdue = $voucher->due_date < now()->toDateString();
                $paidAmount  = round(floatval($voucher->paid_amount ?? 0), 2);
                $relevantTotal = $isOverdue
                    ? round(floatval($voucher->total_with_fine), 2)
                    : round(floatval($voucher->fee_amount), 2);
                $voucher->remaining_amount      = max(0, $relevantTotal - $paidAmount);
                $voucher->total_amount_with_fine = $voucher->remaining_amount; // backward-compat alias
                
                // Also set the amount field for backward compatibility
                $voucher->amount = $voucher->fee_amount;
                
                return $voucher;
            });
            
            // Get reminders sent today count
            $remindersSentToday = FeeVoucher::where('last_reminder_sent', '>=', now()->startOfDay())->count();
            
            return response()->json([
                'success' => true,
                'vouchers' => $vouchers,
                'reminders_sent_today' => $remindersSentToday
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch outstanding vouchers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get voucher statistics with optional filters (Spatie QueryBuilder)
     */
    public function getStatistics(Request $request)
    {
        try {
            // 1. Common Filters (Class, Type)
            $baseQuery = QueryBuilder::for(FeeVoucher::class)
                ->allowedFilters(...[
                    AllowedFilter::partial('class_name'),
                    AllowedFilter::exact('voucher_type'),
                    // Allow these filters but do nothing in base query (handled manually below for split logic)
                    AllowedFilter::callback('date_from', function(){}),
                    AllowedFilter::callback('date_to', function(){}),
                    AllowedFilter::callback('paid_from', function(){}),
                    AllowedFilter::callback('paid_to', function(){}),
                ]);

            // 2. Generation Query (for Total, Unpaid, Generated Amount, Pending)
            $genQuery = clone $baseQuery;
            if ($request->filled('filter.date_from')) {
                $genQuery->whereDate('generated_at', '>=', $request->input('filter.date_from'));
            }
            if ($request->filled('filter.date_to')) {
                $genQuery->whereDate('generated_at', '<=', $request->input('filter.date_to'));
            }

            // 3. Payment Query (for Paid Count, Collected Amount)
            $payQuery = clone $baseQuery;
            $hasPaymentFilter = false;
            
            // If paid_from/to is provided, use them. 
            // If NOT provided but date_from/to IS provided, use date_from/to for payment query too (to align contexts if not explicitly separated)
            // But frontend will be updated to send both identical for "Today"/"Month" stats.
            
            if ($request->filled('filter.paid_from')) {
                $payQuery->whereDate('payment_date', '>=', $request->input('filter.paid_from'));
                $hasPaymentFilter = true;
            } elseif ($request->filled('filter.date_from')) {
                 // Fallback: If looking at "Today", we want paid today
                 $payQuery->whereDate('payment_date', '>=', $request->input('filter.date_from'));
            }

            if ($request->filled('filter.paid_to')) {
                $payQuery->whereDate('payment_date', '<=', $request->input('filter.paid_to'));
                $hasPaymentFilter = true;
            } elseif ($request->filled('filter.date_to')) {
                 $payQuery->whereDate('payment_date', '<=', $request->input('filter.date_to'));
            }

            // Clones for specific counts
            $genQueryForCount = clone $genQuery;
            $genQueryForSum = clone $genQuery;
            $genQueryUnpaid = clone $genQuery;
            $genQueryPending = clone $genQuery;

            $payQueryForCount = clone $payQuery;
            $payQueryForSum = clone $payQuery;

            $stats = [
                'total_vouchers' => $genQueryForCount->count(),
                
                // Paid Vouchers: Count of vouchers PAID in the period (status paid/partial? or just paid)
                // Ususally "Paid Vouchers" implies fully paid.
                'paid_vouchers' => $payQueryForCount->where('status', 'paid')->count(),
                
                'unpaid_vouchers' => $genQueryUnpaid->where('status', 'unpaid')->count(),
                
                // Overdue: Vouchers generated in period that are overdue? Or currently overdue?
                // Visual consistency: Vouchers belonging to this generation batch that are overdue.
                'overdue_vouchers' => (clone $genQuery)->whereIn('status', ['unpaid', 'partially_paid'])
                                                ->where('due_date', '<', now()->toDateString())
                                                ->count(),
                                                
                'total_amount_generated' => $genQueryForSum->sum('total_with_fine'),
                
                // Collected: Sum of payments made in the period
                'total_amount_collected' => $payQueryForSum->sum('paid_amount'),
                
                // Pending: Amount remaining from vouchers GENERATED in this period.
                // Fine only applies to overdue vouchers (due_date < today).
                'pending_amount' => $genQueryPending->whereIn('status', ['unpaid', 'partially_paid'])
                                        ->sum(DB::raw("
                                            CASE
                                                WHEN due_date < CURDATE() THEN COALESCE(total_with_fine, 0) - COALESCE(paid_amount, 0)
                                                ELSE COALESCE(fee_amount, 0) - COALESCE(paid_amount, 0)
                                            END
                                        "))
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send reminders for vouchers
     */
    public function sendReminders(Request $request)
    {
        $request->validate([
            'voucher_ids' => 'required|array',
            'voucher_ids.*' => 'required|exists:fee_vouchers,id',
            'template' => 'required|in:gentle,urgent,final,custom',
            'custom_message' => 'required_if:template,custom|string|max:500',
            'channels' => 'required|array',
            'channels.*' => 'in:sms,whatsapp,email'
        ]);

        try {
            $voucherIds = $request->voucher_ids;
            $template = $request->template;
            $customMessage = $request->custom_message;
            $channels = $request->channels;
            
            $vouchers = FeeVoucher::whereIn('id', $voucherIds)->get();
            $sentCount = 0;
            $failedCount = 0;

            foreach ($vouchers as $voucher) {
                try {
                    // Prepare message based on template
                    $message = $this->prepareReminderMessage($voucher, $template, $customMessage);
                    
                    // Send via selected channels
                    $sent = false;
                    
                    if (in_array('sms', $channels)) {
                        $sent = $this->sendSMS($voucher->parent_phone, $message) || $sent;
                    }
                    
                    if (in_array('whatsapp', $channels)) {
                        $sent = $this->sendWhatsApp($voucher->parent_phone, $message) || $sent;
                    }
                    
                    if (in_array('email', $channels)) {
                        $sent = $this->sendEmail($voucher->parent_email, $message, $voucher) || $sent;
                    }
                    
                    if ($sent) {
                        // Update last reminder sent date
                        $voucher->update(['last_reminder_sent' => now()]);
                        $sentCount++;
                    } else {
                        $failedCount++;
                    }
                    
                } catch (\Exception $e) {
                    $failedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Reminders processed: {$sentCount} sent, {$failedCount} failed",
                'sent_count' => $sentCount,
                'failed_count' => $failedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reprint voucher - Return voucher data for frontend printing
     */
    public function reprintVoucher($id)
    {
        try {
            $voucher = FeeVoucher::findOrFail($id);
            
            // Return voucher data in the same format as generation
            return response()->json([
                'success' => true,
                'voucher' => [
                    'id' => $voucher->id,
                    'voucher_number' => $voucher->voucher_number,
                    'student_id' => $voucher->student_id,
                    'student_name' => $voucher->student_name,
                    'admission_number' => $voucher->admission_number,
                    'parent_name' => $voucher->parent_name,
                    'parent_phone' => $voucher->parent_phone,
                    'parent_email' => $voucher->parent_email,
                    'class_name' => $voucher->class_name,
                    'fee_amount' => $voucher->fee_amount,
                    'fine_amount' => $voucher->fine_amount,
                    'total_with_fine' => $voucher->total_with_fine,
                    'paid_amount' => $voucher->paid_amount, // Include paid amount
                    'payment_date' => $voucher->payment_date, // Include payment date
                    'due_date' => $voucher->due_date,
                    'voucher_type' => $voucher->voucher_type,
                    'fee_month' => $voucher->fee_month,
                    'fee_breakdown' => $voucher->fee_breakdown, // Already decoded by Laravel cast
                    'notes' => $voucher->notes,
                    'status' => $voucher->status,
                    'generated_date' => $voucher->generated_at ? $voucher->generated_at->toISOString() : $voucher->created_at->toISOString()
                ]
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get voucher data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prepare reminder message based on template
     */
    private function prepareReminderMessage($voucher, $template, $customMessage = null)
    {
        $templates = [
            'gentle' => "Dear {parent_name}, this is a gentle reminder that the school fee of Rs. {amount} for {student_name} (Voucher #{voucher_number}) is due on {due_date}. Please make the payment at your earliest convenience. Thank you.",
            
            'urgent' => "URGENT REMINDER: The school fee of Rs. {amount} for {student_name} (Voucher #{voucher_number}) is overdue. Please make immediate payment to avoid additional charges.",
            
            'final' => "FINAL NOTICE: This is the final reminder for overdue school fee of Rs. {amount} for {student_name} (Voucher #{voucher_number}). Please pay immediately to avoid further action.",
            
            'custom' => $customMessage
        ];

        $message = $templates[$template] ?? $templates['gentle'];

        // Replace placeholders
        $replacements = [
            '{parent_name}' => $voucher->parent_name,
            '{student_name}' => $voucher->student_name,
            '{amount}' => number_format($voucher->total_with_fine, 0),
            '{voucher_number}' => $voucher->voucher_number,
            '{due_date}' => Carbon::parse($voucher->due_date)->format('d M Y'),
            '{school_name}' => config('app.name', 'School'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Send SMS reminder (placeholder implementation)
     */
    private function sendSMS($phone, $message)
    {
        // Log SMS sending (replace with actual SMS service)
        Log::info("SMS sent to {$phone}: {$message}");
        return true;
    }

    /**
     * Send WhatsApp reminder (placeholder implementation)
     */
    private function sendWhatsApp($phone, $message)
    {
        // Log WhatsApp sending (replace with actual WhatsApp service)
        Log::info("WhatsApp sent to {$phone}: {$message}");
        return true;
    }

    /**
     * Send email reminder (placeholder implementation)
     */
    private function sendEmail($email, $message, $voucher)
    {
        if (!$email) {
            return false;
        }
        
        // Log email sending (replace with actual email service)
        Log::info("Email sent to {$email}: {$message}");
        return true;
    }
    
    /**
     * Get voucher settings and school information
     */
    public function getSettings()
    {
        try {
            // Fetch school settings from database
            $settingsCollection = Settings::whereIn('setting_key', [
                'school_name',
                'address',  // Actual key in database
                'phone',    // Actual key in database
                'school_logo',
                'school_email',
                'website',  // Actual key in database
                'tagline',  // Additional field from database
                'default_fine_amount',
                'default_due_days'
            ])->pluck('setting_value', 'setting_key');

            return response()->json([
                'success' => true,
                'settings' => [
                    'school_name' => $settingsCollection->get('school_name', config('app.name', 'School Name')),
                    'school_address' => $settingsCollection->get('address', 'School Address'), // Map address to school_address
                    'school_phone' => $settingsCollection->get('phone', 'Phone Number'),       // Map phone to school_phone
                    'school_logo' => $settingsCollection->get('school_logo', 'images/default-logo.png'),
                    'school_email' => $settingsCollection->get('school_email', ''),
                    'school_website' => $settingsCollection->get('website', ''),               // Map website to school_website
                    'school_tagline' => $settingsCollection->get('tagline', ''),               // Add tagline
                    'default_fine_amount' => (int) $settingsCollection->get('default_fine_amount', 0),
                    'default_due_days' => (int) $settingsCollection->get('default_due_days', 30)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Save voucher settings (placeholder)
     */
    public function saveSettings(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully'
        ]);
    }
    
    /**
     * Print multiple vouchers (placeholder)
     */
    public function printVouchers(Request $request)
    {
        $voucherIds = $request->voucher_ids ?? [];
        
        return response()->json([
            'success' => true,
            'message' => count($voucherIds) . ' vouchers prepared for printing',
            'print_url' => '/vouchers/print/' . implode(',', $voucherIds)
        ]);
    }

    /**
     * Check for existing vouchers to prevent duplicates
     */
    public function checkExistingVouchers(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_ids' => 'required|array',
                'student_ids.*' => 'integer|exists:students,id',
                'due_date' => 'required|date',
                'voucher_type' => 'required|string|in:monthly,custom,multiple',
                'fee_month' => 'nullable|date_format:Y-m'
            ]);

            $query = FeeVoucher::with('student')
                ->whereIn('student_id', $validated['student_ids'])
                ->where('due_date', $validated['due_date']);

            // For monthly fees, also check the fee month
            if ($validated['voucher_type'] === 'monthly' && !empty($validated['fee_month'])) {
                $query->where('fee_month', $validated['fee_month']);
            }

            $existingVouchers = $query->get();

            $formattedExisting = $existingVouchers->map(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'student_id' => $voucher->student_id,
                    'student_name' => $voucher->student->name ?? 'Unknown',
                    'due_date' => $voucher->due_date,
                    'fee_month' => $voucher->fee_month,
                    'voucher_type' => $voucher->voucher_type,
                    'status' => $voucher->status
                ];
            });

            return response()->json([
                'success' => true,
                'existing_vouchers' => $formattedExisting,
                'count' => $existingVouchers->count(),
                'message' => $existingVouchers->count() > 0 
                    ? $existingVouchers->count() . ' duplicate voucher(s) found'
                    : 'No duplicate vouchers found'
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking existing vouchers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check existing vouchers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate fee reports (8 types)
     */
    public function reports(Request $request)
    {
        try {
            $type = (int) $request->input('type', 1);
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $className = $request->input('class_name');
            $status = $request->input('status');
            $studentId = $request->input('student_id');
            $voucherType = $request->input('voucher_type');
            $groupBy = $request->input('group_by', 'daily'); // daily, weekly, monthly

            $query = FeeVoucher::query();

            // Common filters
            if ($dateFrom && $dateTo) {
                $query->whereBetween('generated_at', [$dateFrom, $dateTo]);
            }
            if ($className) {
                $query->byClass($className);
            }

            $summary = [];
            $records = [];

            switch ($type) {
                case 1: // Voucher Generation Report
                    if ($voucherType) {
                        $query->where('voucher_type', $voucherType);
                    }

                    $baseQuery = clone $query;
                    $summary = [
                        'total_vouchers' => (clone $baseQuery)->count(),
                        'total_amount' => round((clone $baseQuery)->sum('total_with_fine'), 2),
                        'total_fee_amount' => round((clone $baseQuery)->sum('fee_amount'), 2),
                        'total_fine_amount' => round((clone $baseQuery)->sum('fine_amount'), 2),
                        'by_type' => (clone $baseQuery)->selectRaw('voucher_type, count(*) as count, sum(total_with_fine) as total')
                            ->groupBy('voucher_type')->get(),
                        'by_class' => (clone $baseQuery)->selectRaw('class_name, count(*) as count, sum(total_with_fine) as total')
                            ->groupBy('class_name')->get(),
                    ];

                    $records = $query->orderBy('generated_at', 'desc')
                        ->paginate($request->input('per_page', 25));
                    break;

                case 2: // Due vs Paid Report
                    if ($status) {
                        $query->where('status', $status);
                    }

                    $baseQuery = clone $query;
                    $summary = [
                        'total_vouchers' => (clone $baseQuery)->count(),
                        'total_amount' => round((clone $baseQuery)->sum('total_with_fine'), 2),
                        'total_paid' => round((clone $baseQuery)->sum('paid_amount'), 2),
                        'total_remaining' => round((clone $baseQuery)->sum(DB::raw("
                            CASE WHEN due_date < CURDATE() THEN COALESCE(total_with_fine, 0) ELSE COALESCE(fee_amount, 0) END
                        ")) - (clone $baseQuery)->sum('paid_amount'), 2),
                        'by_status' => (clone $baseQuery)->selectRaw("status, count(*) as count, sum(total_with_fine) as total, sum(paid_amount) as paid")
                            ->groupBy('status')->get(),
                        'on_time_count' => (clone $baseQuery)->where('status', 'paid')
                            ->whereRaw('payment_date <= due_date')->count(),
                        'late_count' => (clone $baseQuery)->where('status', 'paid')
                            ->whereRaw('payment_date > due_date')->count(),
                    ];

                    $records = $query->orderBy('generated_at', 'desc')
                        ->paginate($request->input('per_page', 25));
                    break;

                case 3: // Overdue Defaulters Report
                    $query->whereIn('status', ['unpaid', 'partially_paid'])
                        ->where('due_date', '<', now()->toDateString());

                    if ($className) {
                        $query->byClass($className);
                    }

                    $baseQuery = clone $query;
                    $now = now()->toDateString();

                    $summary = [
                        'total_defaulters' => (clone $baseQuery)->count(),
                        'total_outstanding' => round((clone $baseQuery)->sum('total_with_fine') - (clone $baseQuery)->sum('paid_amount'), 2),
                        'aging_1_30' => (clone $baseQuery)->where('due_date', '>=', now()->subDays(30)->toDateString())->count(),
                        'aging_31_60' => (clone $baseQuery)->where('due_date', '<', now()->subDays(30)->toDateString())
                            ->where('due_date', '>=', now()->subDays(60)->toDateString())->count(),
                        'aging_60_plus' => (clone $baseQuery)->where('due_date', '<', now()->subDays(60)->toDateString())->count(),
                        'by_class' => (clone $baseQuery)->selectRaw('class_name, count(*) as count, sum(total_with_fine - paid_amount) as outstanding')
                            ->groupBy('class_name')->get(),
                    ];

                    $records = $query->with('student')
                        ->orderBy('due_date', 'asc')
                        ->paginate($request->input('per_page', 25));
                    break;

                case 4: // Collection Report
                    $query->where('status', 'paid')
                        ->whereNotNull('payment_date');

                    if ($dateFrom && $dateTo) {
                        $query->whereBetween('payment_date', [$dateFrom, $dateTo]);
                    }

                    $baseQuery = clone $query;

                    // Determine group format based on group_by param
                    $dateFormat = match ($groupBy) {
                        'weekly' => '%x-%v',
                        'monthly' => '%Y-%m',
                        default => '%Y-%m-%d',
                    };

                    $summary = [
                        'total_collected' => round((clone $baseQuery)->sum('paid_amount'), 2),
                        'total_transactions' => (clone $baseQuery)->count(),
                        'avg_transaction' => round((clone $baseQuery)->avg('paid_amount'), 2),
                        'by_period' => (clone $baseQuery)->selectRaw(
                            "DATE_FORMAT(payment_date, '{$dateFormat}') as period, count(*) as count, sum(paid_amount) as total"
                        )->groupBy('period')->orderBy('period')->get(),
                        'by_class' => (clone $baseQuery)->selectRaw('class_name, sum(paid_amount) as total, count(*) as count')
                            ->groupBy('class_name')->get(),
                    ];

                    $records = $query->orderBy('payment_date', 'desc')
                        ->paginate($request->input('per_page', 25));
                    break;

                case 5: // Cancellation / Adjustment Report
                    $query->where('status', 'cancelled');

                    $baseQuery = clone $query;
                    $summary = [
                        'total_cancelled' => (clone $baseQuery)->count(),
                        'total_amount' => round((clone $baseQuery)->sum('total_with_fine'), 2),
                        'by_class' => (clone $baseQuery)->selectRaw('class_name, count(*) as count, sum(total_with_fine) as total')
                            ->groupBy('class_name')->get(),
                        'by_month' => (clone $baseQuery)->selectRaw("DATE_FORMAT(generated_at, '%Y-%m') as month, count(*) as count")
                            ->groupBy('month')->orderBy('month')->get(),
                    ];

                    $records = $query->orderBy('generated_at', 'desc')
                        ->paginate($request->input('per_page', 25));
                    break;

                case 6: // Discount & Concession Report
                    // Note: No dedicated discount column exists. This report identifies
                    // vouchers with fee_amount = 0 or custom_amount significantly lower than standard
                    $query->where(function ($q) {
                        $q->where('fee_amount', 0)
                          ->orWhere('voucher_type', 'custom');
                    });

                    $baseQuery = clone $query;
                    $summary = [
                        'total_discounted' => (clone $baseQuery)->count(),
                        'total_fee_waived' => round((clone $baseQuery)->sum('fee_amount'), 2),
                        'by_class' => (clone $baseQuery)->selectRaw('class_name, count(*) as count, sum(fee_amount) as waived')
                            ->groupBy('class_name')->get(),
                    ];

                    $records = $query->orderBy('generated_at', 'desc')
                        ->paginate($request->input('per_page', 25));
                    break;

                case 7: // Fine / Late Fee Report
                    $baseQuery = clone $query;
                    $summary = [
                        'total_fines_applied' => round((clone $baseQuery)->sum('fine_amount'), 2),
                        'vouchers_with_fines' => (clone $baseQuery)->where('fine_amount', '>', 0)->count(),
                        'fines_paid' => round((clone $baseQuery)->where('status', 'paid')->where('fine_amount', '>', 0)->sum('fine_amount'), 2),
                        'fines_unpaid' => round((clone $baseQuery)->whereIn('status', ['unpaid', 'partially_paid', 'cancelled'])->where('fine_amount', '>', 0)->sum('fine_amount'), 2),
                        'by_class' => (clone $baseQuery)->where('fine_amount', '>', 0)
                            ->selectRaw('class_name, count(*) as count, sum(fine_amount) as total_fines')
                            ->groupBy('class_name')->get(),
                        'frequent_late_payers' => (clone $baseQuery)->where('fine_amount', '>', 0)
                            ->selectRaw('student_id, student_name, class_name, parent_phone, count(*) as late_count, sum(fine_amount) as total_fines')
                            ->groupBy('student_id', 'student_name', 'class_name', 'parent_phone')
                            ->orderBy('late_count', 'desc')
                            ->limit(10)->get(),
                    ];

                    $records = $query->where('fine_amount', '>', 0)
                        ->orderBy('generated_at', 'desc')
                        ->paginate($request->input('per_page', 25));
                    break;

                case 8: // Student Ledger / Statement
                    if (!$studentId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'student_id is required for student ledger'
                        ], 422);
                    }

                    $query->where('student_id', $studentId);

                    $baseQuery = clone $query;
                    $summary = [
                        'student_name' => (clone $baseQuery)->value('student_name'),
                        'class_name' => (clone $baseQuery)->value('class_name'),
                        'admission_number' => (clone $baseQuery)->value('admission_number'),
                        'parent_name' => (clone $baseQuery)->value('parent_name'),
                        'total_charged' => round((clone $baseQuery)->sum('total_with_fine'), 2),
                        'total_paid' => round((clone $baseQuery)->sum('paid_amount'), 2),
                        'balance' => round((clone $baseQuery)->sum(DB::raw("
                            CASE WHEN due_date < CURDATE() THEN COALESCE(total_with_fine, 0) ELSE COALESCE(fee_amount, 0) END
                        ")) - (clone $baseQuery)->sum('paid_amount'), 2),
                        'total_vouchers' => (clone $baseQuery)->count(),
                        'paid_vouchers' => (clone $baseQuery)->where('status', 'paid')->count(),
                        'unpaid_vouchers' => (clone $baseQuery)->whereIn('status', ['unpaid', 'partially_paid'])->count(),
                    ];

                    $records = $query->orderBy('generated_at', 'desc')->get();
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid report type'
                    ], 422);
            }

            return response()->json([
                'success' => true,
                'type' => $type,
                'summary' => $summary,
                'records' => $records,
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating fee report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }
}
