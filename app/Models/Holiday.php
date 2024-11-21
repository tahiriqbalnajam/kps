<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['holiday_date', 'description'];

    public function scopeHolidayDate(Builder $query, $date): Builder
    {
        return $query->whereMonth('holiday_date', $date);
    }
}
