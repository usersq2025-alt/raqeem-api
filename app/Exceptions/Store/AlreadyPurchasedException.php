<?php

namespace App\Exceptions\Store;

use App\Exceptions\Gameplay\GameplayException;

class AlreadyPurchasedException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('هذا العنصر مُشترى بالفعل لهذا الطالب', 409);
    }
}
