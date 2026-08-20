<?php

namespace App\Exceptions\ExcelImport;

use App\Exceptions\Gameplay\GameplayException;

class EmptyExcelFileException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('الملف فارغ أو لا يحتوي صفوف بيانات بعد صف العناوين', 422);
    }
}
