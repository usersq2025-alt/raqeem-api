<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameQuestion extends Model
{
    protected $table = 'game_questions';

    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'question_id',
        'sort_order',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
