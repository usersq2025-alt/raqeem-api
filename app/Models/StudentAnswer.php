<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $table = 'student_answers';

    public $timestamps = false;

    protected $fillable = [
        'attempt_id',
        'game_id',
        'question_id',
        'is_correct',
        'selected_answer',
        'answered_at',
    ];

    protected $casts = [
        'selected_answer' => 'array',
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(StudentLessonAttempt::class, 'attempt_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
