<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeMeta extends Model
{
    use HasFactory;
    protected $table = 'fee_meta';
    protected $fillable = ['meta_key', 'meta_value', 'fee_id'];
}
