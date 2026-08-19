<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class ParentUser extends Model
{
    use HasApiTokens;

    protected $table = 'parents';

    protected $fillable = [
        'public_id',
        'full_name',
        'email',
        'phone_country_code',
        'phone',
        'password_hash',
        'email_verified_at',
        'status',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function otpCodes()
    {
        return $this->hasMany(OtpCode::class, 'parent_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}
