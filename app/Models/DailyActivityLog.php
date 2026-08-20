<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyActivityLog extends Model
{
    protected $table = 'daily_activity_log';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'activity_date',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
