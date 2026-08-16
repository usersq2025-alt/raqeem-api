<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'games';

    protected $fillable = [
        'lesson_id',
        'game_type_id',
        'title',
        'sort_order',
        'randomize_order',
        'config',
        'status',
    ];

    protected $casts = [
        'config' => 'array',
        'randomize_order' => 'boolean',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function gameType()
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }

    public function gameQuestions()
    {
        return $this->hasMany(GameQuestion::class, 'game_id');
    }

    public function studentLessonAttempts()
    {
        return $this->hasMany(StudentLessonAttempt::class, 'current_game_id');
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'game_id');
    }
}
