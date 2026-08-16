<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLessonAttempt extends Model
{
    protected $table = 'student_lesson_attempts';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'lesson_id',
        'attempt_number',
        'status',
        'correct_count',
        'wrong_count',
        'stars',
        'points_earned',
        'current_game_id',
        'current_question_id',
        'recharge_ends_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'recharge_ends_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function currentGame()
    {
        return $this->belongsTo(Game::class, 'current_game_id');
    }

    public function currentQuestion()
    {
        return $this->belongsTo(Question::class, 'current_question_id');
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'attempt_id');
    }
}
