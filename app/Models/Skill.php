<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    const UPDATED_AT = null;

    protected $table = 'skills';

    protected $fillable = [
        'name',
        'description',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class, 'skill_id');
    }
}
