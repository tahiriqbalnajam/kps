<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = ['name','nic', 'education', 'phone', 'address'];
    public function user()
    {
        return $this->belongsTo('User');
    }
}
