<?php

namespace App\Exceptions\Gameplay;

class QuestionNotInAttemptException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('هذا السؤال لا ينتمي لدرس هذه المحاولة', 422);
    }
}
