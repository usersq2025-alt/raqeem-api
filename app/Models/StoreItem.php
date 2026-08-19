<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItem extends Model
{
    const UPDATED_AT = null;

    protected $table = 'store_items';

    protected $fillable = [
        'category',
        'name',
        'description',
        'image_media_id',
        'price_points',
        'unlock_type',
        'unlock_unit_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function imageMedia()
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    public function unlockUnit()
    {
        return $this->belongsTo(Unit::class, 'unlock_unit_id');
    }

    public function studentPurchases()
    {
        return $this->hasMany(StudentPurchase::class, 'store_item_id');
    }

    public function unitCompletionRewards()
    {
        return $this->hasMany(UnitCompletionReward::class, 'store_item_id');
    }

    public function studentGiftsLog()
    {
        return $this->hasMany(StudentGiftLog::class, 'store_item_id');
    }
}
