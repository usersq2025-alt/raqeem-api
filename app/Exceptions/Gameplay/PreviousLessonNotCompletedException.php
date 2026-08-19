<?php

namespace App\Exceptions\Gameplay;

class PreviousLessonNotCompletedException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('يجب إكمال الدرس السابق بهذه الوحدة أولاً', 422);
    }
}
