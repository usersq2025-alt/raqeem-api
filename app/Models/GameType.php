<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameType extends Model
{
    protected $table = 'game_types';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class, 'game_type_id');
    }

    public function games()
    {
        return $this->hasMany(Game::class, 'game_type_id');
    }
}
