<?php

namespace App\Services\Grading;

interface AnswerGrader
{
    /**
     * $payload هو Question.payload (مصفوفة بعد الـ cast)، و$selectedAnswer هو
     * ما أرسله الطالب (StudentAnswer.selected_answer قبل الحفظ).
     */
    public function isCorrect(array $payload, mixed $selectedAnswer): bool;
}
