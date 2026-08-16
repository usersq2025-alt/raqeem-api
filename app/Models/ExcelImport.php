<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelImport extends Model
{
    protected $table = 'excel_imports';

    protected $fillable = [
        'admin_user_id',
        'file_name',
        'status',
        'total_rows',
        'success_rows',
        'error_rows',
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    public function excelImportRows()
    {
        return $this->hasMany(ExcelImportRow::class, 'import_id');
    }
}
