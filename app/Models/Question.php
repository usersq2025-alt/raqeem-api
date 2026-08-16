<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';

    protected $fillable = [
        'lesson_id',
        'skill_id',
        'game_type_id',
        'question_text',
        'image_media_id',
        'difficulty',
        'payload',
        'explanation',
        'status',
        'source',
        'created_by',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    public function gameType()
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }

    public function imageMedia()
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function gameQuestions()
    {
        return $this->hasMany(GameQuestion::class, 'question_id');
    }

    public function studentLessonAttempts()
    {
        return $this->hasMany(StudentLessonAttempt::class, 'current_question_id');
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'question_id');
    }

    public function reviewStationQuestions()
    {
        return $this->hasMany(ReviewStationQuestion::class, 'question_id');
    }

    public function excelImportRows()
    {
        return $this->hasMany(ExcelImportRow::class, 'created_question_id');
    }
}
