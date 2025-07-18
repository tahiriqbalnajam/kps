<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Import extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'filename',
        'file_path',
        'status',
        'total_records',
        'processed_records',
        'successful_records',
        'failed_records',
        'errors',
        'user_id'
    ];

    protected $casts = [
        'errors' => 'array',
        'total_records' => 'integer',
        'processed_records' => 'integer',
        'successful_records' => 'integer',
        'failed_records' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
