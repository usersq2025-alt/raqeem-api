<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profession extends Model
{
    protected $table = 'professions';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'avatar_asset_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'profession_id');
    }
}
