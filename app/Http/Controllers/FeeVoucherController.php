<?php

namespace App\Http\Controllers;

use App\Models\FeeVoucher;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Settings;
use Carbon\Carbon;

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
                    $query->where('status', $filters['status']);
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

            DB::beginTransaction();
            
            $savedVouchers = [];
            $voucherNumber = 'FV-' . now()->format('YmdHis');
            
            foreach ($request->vouchers as $index => $voucherData) {
                $voucher = FeeVoucher::create([
                    'voucher_number' => $voucherNumber . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
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
            
            return response()->json([
                'success' => true,
                'message' => count($savedVouchers) . ' vouchers generated successfully',
                'saved_vouchers' => $savedVouchers
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate vouchers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of generated vouchers
     */
    public function getVouchers(Request $request)
    {
        try {
            $query = FeeVoucher::query();
            
            // Apply filters
            if ($request->filled('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('class_name') && !empty($request->class_name)) {
                $query->where('class_name', 'LIKE', "%{$request->class_name}%");
            }
            
            if ($request->filled('date_from') && !empty($request->date_from)) {
                $query->whereDate('generated_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to') && !empty($request->date_to)) {
                $query->whereDate('generated_at', '<=', $request->date_to);
            }
            
            if ($request->filled('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('voucher_number', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('student_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('admission_number', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // Handle overdue_only parameter for tracking page
            if ($request->boolean('overdue_only')) {
                $query->where('status', 'unpaid')
                      ->where('due_date', '<', now()->toDateString());
            }
            
            $vouchers = $query->orderBy('generated_at', 'desc')
                             ->paginate($request->input('limit', 15));
            
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
     * Update voucher status (paid/unpaid)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:paid,unpaid,cancelled',
                'paid_amount' => 'nullable|numeric|min:0',
                'payment_date' => 'nullable|date'
            ]);

            $voucher = FeeVoucher::findOrFail($id);
            
            $voucher->update([
                'status' => $request->status,
                'paid_amount' => $request->paid_amount,
                'payment_date' => $request->payment_date,
                'updated_by' => Auth::id() ?? 1,
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Voucher status updated successfully',
                'voucher' => $voucher
            ]);
            
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
            
            // Only allow deletion of unpaid vouchers
            if ($voucher->status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete paid vouchers'
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
            $query = FeeVoucher::where('status', 'unpaid');
            
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
                    $q->where('student_name', 'like', "%{$search}%")
                      ->orWhere('voucher_number', 'like', "%{$search}%")
                      ->orWhere('parent_name', 'like', "%{$search}%");
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
     * Get voucher statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $stats = [
                'total_vouchers' => FeeVoucher::count(),
                'paid_vouchers' => FeeVoucher::where('status', 'paid')->count(),
                'unpaid_vouchers' => FeeVoucher::where('status', 'unpaid')->count(),
                'overdue_vouchers' => FeeVoucher::where('status', 'unpaid')
                                                ->where('due_date', '<', now()->toDateString())
                                                ->count(),
                'total_amount_generated' => FeeVoucher::sum('total_with_fine'),
                'total_amount_collected' => FeeVoucher::where('status', 'paid')->sum('paid_amount'),
                'pending_amount' => FeeVoucher::where('status', 'unpaid')->sum('total_with_fine')
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
                    'default_fine_amount' => (int) $settingsCollection->get('default_fine_amount', 100),
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
}
