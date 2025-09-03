<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiReport extends Model
{
    protected $fillable = [
        'title',
        'prompt',
        'data_sources',
        'analysis_result',
        'metadata',
        'status'
    ];

    protected $casts = [
        'data_sources' => 'array',
        'metadata' => 'array'
    ];
}
