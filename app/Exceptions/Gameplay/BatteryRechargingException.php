<?php

namespace App\Exceptions\Gameplay;

class BatteryRechargingException extends GameplayException
{
    public function __construct(int $remainingSeconds)
    {
        parent::__construct(
            'البطارية فارغة، يجب الانتظار حتى تُشحن',
            423,
            ['retry_after_seconds' => max(0, $remainingSeconds)]
        );
    }
}
