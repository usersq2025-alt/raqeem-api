<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotivationalPhrase extends Model
{
    protected $table = 'motivational_phrases';

    public $timestamps = false;

    protected $fillable = [
        'text_ar',
        'text_en',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
