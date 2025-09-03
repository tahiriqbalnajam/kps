<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeVoucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voucher_number',
        'student_id',
        'student_name',
        'admission_number',
        'parent_name',
        'parent_phone',
        'parent_email',
        'class_name',
        'fee_amount',
        'fine_amount',
        'total_with_fine',
        'due_date',
        'voucher_type',
        'custom_amount',
        'fee_month',
        'fee_breakdown',
        'selected_fee_types',
        'notes',
        'status',
        'paid_amount',
        'payment_date',
        'last_reminder_sent',
        'generated_by',
        'generated_at',
        'updated_by'
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'total_with_fine' => 'decimal:2',
        'custom_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'last_reminder_sent' => 'datetime',
        'generated_at' => 'datetime',
        'fee_breakdown' => 'json',
        'selected_fee_types' => 'json'
    ];

    protected $dates = [
        'due_date',
        'payment_date',
        'generated_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'unpaid')
                    ->where('due_date', '<', now()->toDateString());
    }

    public function scopeByClass($query, $className)
    {
        return $query->where('class_name', 'LIKE', "%{$className}%");
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('generated_at', [$startDate, $endDate]);
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->status === 'unpaid' && $this->due_date < now()->toDateString();
    }

    public function getDaysOverdueAttribute()
    {
        if ($this->is_overdue) {
            return now()->diffInDays($this->due_date);
        }
        return 0;
    }

    public function getFormattedVoucherNumberAttribute()
    {
        return strtoupper($this->voucher_number);
    }

    // Mutators
    public function setVoucherNumberAttribute($value)
    {
        $this->attributes['voucher_number'] = strtoupper($value);
    }
}
