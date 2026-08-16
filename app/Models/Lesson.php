<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $table = 'lessons';

    protected $fillable = [
        'unit_id',
        'title',
        'body_content',
        'sort_order',
        'status',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'lesson_id');
    }

    public function games()
    {
        return $this->hasMany(Game::class, 'lesson_id');
    }

    public function studentLessonAttempts()
    {
        return $this->hasMany(StudentLessonAttempt::class, 'lesson_id');
    }
}
