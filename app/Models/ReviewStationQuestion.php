<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewStationQuestion extends Model
{
    protected $table = 'review_station_questions';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'question_id',
        'is_correct',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(ReviewStationSession::class, 'session_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
