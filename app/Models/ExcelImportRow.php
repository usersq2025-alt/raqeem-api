<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelImportRow extends Model
{
    protected $table = 'excel_import_rows';

    public $timestamps = false;

    protected $fillable = [
        'import_id',
        'row_number',
        'raw_data',
        'mapped_data',
        'status',
        'error_message',
        'created_question_id',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'mapped_data' => 'array',
    ];

    public function import()
    {
        return $this->belongsTo(ExcelImport::class, 'import_id');
    }

    public function createdQuestion()
    {
        return $this->belongsTo(Question::class, 'created_question_id');
    }
}
