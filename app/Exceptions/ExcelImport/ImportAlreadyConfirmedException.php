<?php

namespace App\Exceptions\ExcelImport;

use App\Exceptions\Gameplay\GameplayException;

class ImportAlreadyConfirmedException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('هذا الاستيراد مؤكَّد ومنشور بالفعل، لا يمكن تعديله', 422);
    }
}
