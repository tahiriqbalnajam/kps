<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['title'];

    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'class_id');
    }
}
