<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;
    protected $fillable = ['account_id', 'naam', 'jama', 'balance'];

    public function accounts() {
        return $this->belongsTo(User::class, 'user_id')->select(['id', 'name','email','status','phone']);
    }
}
