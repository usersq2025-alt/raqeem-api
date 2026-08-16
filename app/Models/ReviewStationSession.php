<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewStationSession extends Model
{
    protected $table = 'review_station_sessions';

    protected $fillable = [
        'student_id',
        'unit_id',
        'status',
        'points_earned',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function reviewStationQuestions()
    {
        return $this->hasMany(ReviewStationQuestion::class, 'session_id');
    }
}
