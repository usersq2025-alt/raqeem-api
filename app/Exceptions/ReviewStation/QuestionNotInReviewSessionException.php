<?php

namespace App\Exceptions\ReviewStation;

use App\Exceptions\Gameplay\GameplayException;

class QuestionNotInReviewSessionException extends GameplayException
{
    public function __construct()
    {
        parent::__construct('هذا السؤال لا ينتمي لجلسة المراجعة هذه', 422);
    }
}
