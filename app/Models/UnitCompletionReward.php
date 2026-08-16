<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitCompletionReward extends Model
{
    protected $table = 'unit_completion_rewards';

    public $timestamps = false;

    protected $fillable = [
        'unit_id',
        'reward_type',
        'points_amount',
        'store_item_id',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function storeItem()
    {
        return $this->belongsTo(StoreItem::class, 'store_item_id');
    }
}
