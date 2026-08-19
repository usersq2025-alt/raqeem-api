<?php

namespace App\Exceptions\Store;

use App\Exceptions\Gameplay\GameplayException;

class ItemNotAvailableException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('هذا العنصر غير متاح حاليًا', 422);
    }
}
