<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $table = 'badges';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'threshold_type',
        'threshold_value',
        'icon_url',
    ];

    public function studentBadges()
    {
        return $this->hasMany(StudentBadge::class, 'badge_id');
    }
}
