<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'public_id',
        'parent_id',
        'full_name',
        'birth_date',
        'grade_id',
        'gender',
        'profession_id',
        'points_balance',
        'streak_current',
        'streak_longest',
        'last_activity_date',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'datetime',
        'last_activity_date' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(ParentUser::class, 'parent_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function profession()
    {
        return $this->belongsTo(Profession::class, 'profession_id');
    }

    public function studentLessonAttempts()
    {
        return $this->hasMany(StudentLessonAttempt::class, 'student_id');
    }

    public function reviewStationSessions()
    {
        return $this->hasMany(ReviewStationSession::class, 'student_id');
    }

    public function dailyActivityLog()
    {
        return $this->hasMany(DailyActivityLog::class, 'student_id');
    }

    public function studentBadges()
    {
        return $this->hasMany(StudentBadge::class, 'student_id');
    }

    public function pointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class, 'student_id');
    }

    public function studentPurchases()
    {
        return $this->hasMany(StudentPurchase::class, 'student_id');
    }

    public function studentGiftsLog()
    {
        return $this->hasMany(StudentGiftLog::class, 'student_id');
    }
}
