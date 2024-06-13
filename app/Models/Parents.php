<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parents extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'name',
        'phone',
        'password',
        'address',
        'profession',
        'cnic',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id', 'id')->select(['id','name','parent_id']);
    }

}
