<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 
        'category', 
        'amount', 
        'description', 
        'date', 
        'reference_number',
        'payment_method',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2'
    ];

    protected $dates = ['deleted_at'];

    // Constants for transaction types
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    // Constants for payment methods
    const PAYMENT_CASH = 'cash';
    const PAYMENT_BANK = 'bank';
    const PAYMENT_CHEQUE = 'cheque';
    const PAYMENT_ONLINE = 'online';

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function scopeIncome($query)
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
