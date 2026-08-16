<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGiftLog extends Model
{
    protected $table = 'student_gifts_log';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'unit_id',
        'reward_type',
        'points_amount',
        'store_item_id',
        'granted_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function storeItem()
    {
        return $this->belongsTo(StoreItem::class, 'store_item_id');
    }
}
