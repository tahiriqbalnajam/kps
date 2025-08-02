<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;

class AccountingController extends Controller
{
    const ITEM_PER_PAGE = 15;

    /**
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = $request->get('keyword', '');
        $type = $request->get('type', '');
        $category = $request->get('category', '');
        $dateRange = $request->get('date', []);

        $query = AccountTransaction::with('createdBy:id,name');

        // Apply filters
        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('description', 'like', '%' . $keyword . '%')
                  ->orWhere('reference_number', 'like', '%' . $keyword . '%')
                  ->orWhere('category', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($type)) {
            $query->where('type', $type);
        }

        if (!empty($category)) {
            $query->where('category', $category);
        }

        if (!empty($dateRange) && count($dateRange) == 2) {
            $startDate = Carbon::parse($dateRange[0])->startOfDay();
            $endDate = Carbon::parse($dateRange[1])->endOfDay();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $transactions = $query->orderBy('date', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->paginate($limit);

        // Calculate totals
        $totalIncome = AccountTransaction::where('type', 'income')->sum('amount');
        $totalExpense = AccountTransaction::where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Calculate filtered totals
        $filteredQuery = clone $query;
        $filteredIncome = $filteredQuery->where('type', 'income')->sum('amount');
        $filteredExpense = $filteredQuery->where('type', 'expense')->sum('amount');

        return response()->json(new JsonResponse([
            'transactions' => $transactions,
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'balance' => $balance,
                'filtered_income' => $filteredIncome,
                'filtered_expense' => $filteredExpense
            ]
        ]));
    }

    /**
     * Store a newly created transaction
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:1000',
            'date' => 'required|date',
            'payment_method' => 'required|in:cash,bank,cheque,online',
            'reference_number' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(new JsonResponse([], $validator->errors()->first()), 422);
        }

        try {
            // Try to get authenticated user ID, fallback to first user if needed
            $userId = Auth::id();
            if (!$userId) {
                // Fallback: get the first user ID from the database for development
                $userId = \App\Models\User::first()->id ?? 1;
            }

            $transaction = AccountTransaction::create([
                'type' => $request->type,
                'category' => $request->category,
                'amount' => $request->amount,
                'description' => $request->description,
                'date' => $request->date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'created_by' => $userId
            ]);

            return response()->json(new JsonResponse($transaction, 'Transaction created successfully'));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse([], 'Error creating transaction: ' . $e->getMessage()), 500);
        }
    }

    /**
     * Display the specified transaction
     */
    public function show($id)
    {
        $transaction = AccountTransaction::with('createdBy:id,name')->findOrFail($id);
        return response()->json(new JsonResponse($transaction));
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:1000',
            'date' => 'required|date',
            'payment_method' => 'required|in:cash,bank,cheque,online',
            'reference_number' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(new JsonResponse([], $validator->errors()->first()), 422);
        }

        try {
            $transaction = AccountTransaction::findOrFail($id);
            $transaction->update([
                'type' => $request->type,
                'category' => $request->category,
                'amount' => $request->amount,
                'description' => $request->description,
                'date' => $request->date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number
            ]);

            return response()->json(new JsonResponse($transaction, 'Transaction updated successfully'));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse([], 'Error updating transaction'), 500);
        }
    }

    /**
     * Remove the specified transaction
     */
    public function destroy($id)
    {
        try {
            $transaction = AccountTransaction::findOrFail($id);
            $transaction->delete();

            return response()->json(new JsonResponse([], 'Transaction deleted successfully'));
        } catch (\Exception $e) {
            return response()->json(new JsonResponse([], 'Error deleting transaction'), 500);
        }
    }

    /**
     * Get dashboard summary
     */
    public function dashboard()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $currentYear = Carbon::now()->startOfYear();

        $summary = [
            'total_income' => AccountTransaction::where('type', 'income')->sum('amount'),
            'total_expense' => AccountTransaction::where('type', 'expense')->sum('amount'),
            'monthly_income' => AccountTransaction::where('type', 'income')
                                                 ->where('date', '>=', $currentMonth)
                                                 ->sum('amount'),
            'monthly_expense' => AccountTransaction::where('type', 'expense')
                                                  ->where('date', '>=', $currentMonth)
                                                  ->sum('amount'),
            'yearly_income' => AccountTransaction::where('type', 'income')
                                                ->where('date', '>=', $currentYear)
                                                ->sum('amount'),
            'yearly_expense' => AccountTransaction::where('type', 'expense')
                                                 ->where('date', '>=', $currentYear)
                                                 ->sum('amount'),
        ];

        $summary['balance'] = $summary['total_income'] - $summary['total_expense'];
        $summary['monthly_balance'] = $summary['monthly_income'] - $summary['monthly_expense'];
        $summary['yearly_balance'] = $summary['yearly_income'] - $summary['yearly_expense'];

        // Recent transactions
        $recentTransactions = AccountTransaction::with('createdBy:id,name')
                                               ->orderBy('created_at', 'desc')
                                               ->limit(10)
                                               ->get();

        return response()->json(new JsonResponse([
            'summary' => $summary,
            'recent_transactions' => $recentTransactions
        ]));
    }

    /**
     * Get categories
     */
    public function getCategories(Request $request)
    {
        $type = $request->get('type');
        
        $categories = AccountTransaction::select('category')
                                       ->when($type, function($query, $type) {
                                           return $query->where('type', $type);
                                       })
                                       ->groupBy('category')
                                       ->orderBy('category')
                                       ->pluck('category');

        return response()->json(new JsonResponse($categories));
    }
}
