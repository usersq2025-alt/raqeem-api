<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $table = 'grades';

    public $timestamps = false;

    protected $fillable = [
        'level',
        'name_ar',
        'name_en',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'grade_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'grade_id');
    }
}
