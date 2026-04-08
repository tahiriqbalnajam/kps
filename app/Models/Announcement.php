<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'type',
        'content',
        'target_audience',
        'publish_date',
        'expiry_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'publish_date' => 'date:Y-m-d',
        'expiry_date'  => 'date:Y-m-d',
        'is_active'    => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
