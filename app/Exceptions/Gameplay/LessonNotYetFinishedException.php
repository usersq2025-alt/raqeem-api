<?php

namespace App\Exceptions\Gameplay;

class LessonNotYetFinishedException extends GameplayException
{
    public function __construct(int $answered, int $total)
    {
        parent::__construct(
            "لم تتم الإجابة بعد على كل أسئلة الدرس ({$answered}/{$total})",
            422,
            ['answered' => $answered, 'total' => $total]
        );
    }
}
