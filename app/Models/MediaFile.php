<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    protected $table = 'media_files';

    protected $fillable = [
        'url',
        'original_filename',
        'mime_type',
        'size_bytes',
        'uploaded_by',
    ];

    public function uploadedBy()
    {
        return $this->belongsTo(AdminUser::class, 'uploaded_by');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'cover_media_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'image_media_id');
    }

    public function storeItems()
    {
        return $this->hasMany(StoreItem::class, 'image_media_id');
    }
}
