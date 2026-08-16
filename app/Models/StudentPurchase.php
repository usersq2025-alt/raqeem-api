<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPurchase extends Model
{
    protected $table = 'student_purchases';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'store_item_id',
        'price_paid',
        'purchased_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function storeItem()
    {
        return $this->belongsTo(StoreItem::class, 'store_item_id');
    }
}
