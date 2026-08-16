<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';

    public $timestamps = false;

    protected $fillable = [
        'name_ar',
        'name_en',
        'icon_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function units()
    {
        return $this->hasMany(Unit::class, 'subject_id');
    }
}
