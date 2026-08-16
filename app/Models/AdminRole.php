<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $table = 'admin_roles';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
    ];

    public function adminUsers()
    {
        return $this->hasMany(AdminUser::class, 'role_id');
    }
}
