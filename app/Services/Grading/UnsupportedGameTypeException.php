<?php

namespace App\Services\Grading;

use App\Exceptions\Gameplay\GameplayException;

class UnsupportedGameTypeException extends GameplayException
{
    public function __construct(string $gameTypeCode)
    {
        parent::__construct("التصحيح التلقائي غير مدعوم بعد لنمط اللعبة: {$gameTypeCode}", 501);
    }
}
