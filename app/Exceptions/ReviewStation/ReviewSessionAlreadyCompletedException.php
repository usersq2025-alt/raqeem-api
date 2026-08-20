<?php

namespace App\Exceptions\ReviewStation;

use App\Exceptions\Gameplay\GameplayException;

class ReviewSessionAlreadyCompletedException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('جلسة المراجعة هذه مكتملة بالفعل', 422);
    }
}
