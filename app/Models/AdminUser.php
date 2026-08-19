<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Model
{
    use HasApiTokens;

    const UPDATED_AT = null;

    protected $table = 'admin_users';

    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'role_id',
        'status',
        'last_login_at',
        'invited_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'invited_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function role()
    {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    public function mediaFiles()
    {
        return $this->hasMany(MediaFile::class, 'uploaded_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    public function excelImports()
    {
        return $this->hasMany(ExcelImport::class, 'admin_user_id');
    }
}
