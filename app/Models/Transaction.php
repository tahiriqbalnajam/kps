<?php

namespace App\Models;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    protected $fillable = ['jama_id, naam_id, type, sub_type, amount, comments, entry_by, status, sale_id, purchase_id'];

    public function naam_account()
    {
        return $this->belongsTo('App\Models\User','naam_id')->select(array('id', 'name'));

    }
    public function jama_account()
    {
        return $this->belongsTo('App\Models\User','jama_id')->select(array('id', 'name'));

    }
    
}
