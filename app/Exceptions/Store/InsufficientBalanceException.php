<?php

namespace App\Exceptions\Store;

use App\Exceptions\Gameplay\GameplayException;

class InsufficientBalanceException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('الرصيد غير كافٍ لإتمام هذا الشراء', 422);
    }
}
