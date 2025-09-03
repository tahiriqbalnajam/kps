<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccountTransaction;
use App\Models\User;
use Carbon\Carbon;

class AccountTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user to assign as creator
        $user = User::first();
        if (!$user) {
            $this->command->info('No users found. Please create a user first.');
            return;
        }

        $transactions = [
            // Income transactions
            [
                'type' => 'income',
                'category' => 'Student Fees',
                'amount' => 150000.00,
                'description' => 'Monthly tuition fees collection from Class 10 students',
                'date' => Carbon::now()->subDays(5),
                'payment_method' => 'bank',
                'reference_number' => 'TXN001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'income',
                'category' => 'Admission Fees',
                'amount' => 25000.00,
                'description' => 'New student admission fees for current session',
                'date' => Carbon::now()->subDays(10),
                'payment_method' => 'cash',
                'reference_number' => 'ADM001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'income',
                'category' => 'Transportation Fees',
                'amount' => 45000.00,
                'description' => 'Bus transportation fees for current month',
                'date' => Carbon::now()->subDays(3),
                'payment_method' => 'online',
                'reference_number' => 'TRANS001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'income',
                'category' => 'Library Fees',
                'amount' => 8000.00,
                'description' => 'Library usage and book rental fees',
                'date' => Carbon::now()->subDays(7),
                'payment_method' => 'cash',
                'reference_number' => 'LIB001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'income',
                'category' => 'Examination Fees',
                'amount' => 18000.00,
                'description' => 'Mid-term examination fees collection',
                'date' => Carbon::now()->subDays(15),
                'payment_method' => 'bank',
                'reference_number' => 'EXAM001',
                'created_by' => $user->id,
            ],

            // Expense transactions
            [
                'type' => 'expense',
                'category' => 'Teacher Salaries',
                'amount' => 120000.00,
                'description' => 'Monthly salary payment for teaching staff',
                'date' => Carbon::now()->subDays(2),
                'payment_method' => 'bank',
                'reference_number' => 'SAL001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Electricity Bill',
                'amount' => 15000.00,
                'description' => 'Monthly electricity bill payment',
                'date' => Carbon::now()->subDays(4),
                'payment_method' => 'online',
                'reference_number' => 'ELEC001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Office Supplies',
                'amount' => 8500.00,
                'description' => 'Purchase of stationery and office supplies',
                'date' => Carbon::now()->subDays(6),
                'payment_method' => 'cash',
                'reference_number' => 'OFFICE001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Building Maintenance',
                'amount' => 22000.00,
                'description' => 'Classroom renovation and maintenance work',
                'date' => Carbon::now()->subDays(8),
                'payment_method' => 'cheque',
                'reference_number' => 'MAINT001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Internet Bill',
                'amount' => 3500.00,
                'description' => 'Monthly internet service bill',
                'date' => Carbon::now()->subDays(12),
                'payment_method' => 'online',
                'reference_number' => 'NET001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Books & Materials',
                'amount' => 12000.00,
                'description' => 'Purchase of textbooks and teaching materials',
                'date' => Carbon::now()->subDays(14),
                'payment_method' => 'bank',
                'reference_number' => 'BOOKS001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Staff Salaries',
                'amount' => 35000.00,
                'description' => 'Monthly salary payment for administrative staff',
                'date' => Carbon::now()->subDays(2),
                'payment_method' => 'bank',
                'reference_number' => 'STAFF001',
                'created_by' => $user->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Water Bill',
                'amount' => 2500.00,
                'description' => 'Monthly water utility bill',
                'date' => Carbon::now()->subDays(9),
                'payment_method' => 'cash',
                'reference_number' => 'WATER001',
                'created_by' => $user->id,
            ],
        ];

        foreach ($transactions as $transaction) {
            AccountTransaction::create($transaction);
        }

        $this->command->info('Sample accounting transactions created successfully!');
    }
}
