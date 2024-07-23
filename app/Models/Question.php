<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory; 
    public $timestamps = false;
    protected $table = 'chapter_questions';
    protected $fillable = [
        'chapter_id', 'question_text', 'choice_1', 'choice_2', 'choice_3', 'choice_4', 'correct_choice'
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    // public function choices()
    // {
    //     return $this->hasMany(Choice::class);
    // }

}
