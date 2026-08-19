<?php

namespace App\Exceptions\Gameplay;

class AttemptAlreadyCompletedException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('هذه المحاولة مكتملة بالفعل', 422);
    }
}
