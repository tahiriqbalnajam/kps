<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddExam extends Model
{
    protected $table = 'exam_result';
    protected $fillable = ['examname','stdclass'];
}
