<?php

namespace App\Exceptions\Store;

use App\Exceptions\Gameplay\GameplayException;

class ItemLockedException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('هذا العنصر محجوب، أكمل الوحدة المطلوبة أولاً لفتحه', 422);
    }
}
