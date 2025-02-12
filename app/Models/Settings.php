<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = ['school_name', 'address','phone','logo','opening_time',
                            'tagline','email','website','facebook','twitter','instagram','tiktok',
                            'result_header', 'teacher_leaves_allowed'];
}
