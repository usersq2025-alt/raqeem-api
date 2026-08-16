<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'units';

    protected $fillable = [
        'subject_id',
        'grade_id',
        'title',
        'cover_media_id',
        'sort_order',
        'status',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function coverMedia()
    {
        return $this->belongsTo(MediaFile::class, 'cover_media_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'unit_id');
    }

    public function reviewStationSessions()
    {
        return $this->hasMany(ReviewStationSession::class, 'unit_id');
    }

    public function storeItems()
    {
        return $this->hasMany(StoreItem::class, 'unlock_unit_id');
    }

    public function unitCompletionRewards()
    {
        return $this->hasMany(UnitCompletionReward::class, 'unit_id');
    }

    public function studentGiftsLog()
    {
        return $this->hasMany(StudentGiftLog::class, 'unit_id');
    }
}
