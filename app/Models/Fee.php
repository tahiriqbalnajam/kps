<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $table = 'fee';

    public function student(){
        return $this->belongsTo(Student::class,'student_id');
    }

    public function feetype(){
        return $this->belongsTo(FeeType::class,'fee_type_id');
    }

    public function feemeta(){
        return $this->belongsTo(FeeMeta::class,'fee_id');
    }

    public function fee_meta()
    {
        return $this->hasMany(FeeMeta::class,'fee_id');
    }

    public function lastpaid()
    {
        return $this->hasOne(Fee::class)->ofMany([
            'created_at' => 'max',
            'id' => 'max',
        ], function ($query) {
            $query->where('created_at', '<', now());
        });
    }
}
