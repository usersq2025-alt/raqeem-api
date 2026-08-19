<?php

namespace App\Services\Grading;

class McqAnswerGrader implements AnswerGrader
{
    /**
     * لا يوجد Validation Schema رسمي سابق لـ payload بهذا المشروع (كما رصد
     * تقرير التدقيق الأصلي) — هذا هو الشكل المعتمَد لأول مرة لنمط MCQ:
     *
     * payload:          {"options": [{"id": "a", "text": "..."}, ...], "correct_option_id": "a"}
     * selected_answer:  {"selected_option_id": "a"}
     */
    public function isCorrect(array $payload, mixed $selectedAnswer): bool
    {
        $correctOptionId = $payload['correct_option_id'] ?? null;
        $selectedOptionId = is_array($selectedAnswer) ? ($selectedAnswer['selected_option_id'] ?? null) : null;

        if ($correctOptionId === null || $selectedOptionId === null) {
            return false;
        }

        return (string) $correctOptionId === (string) $selectedOptionId;
    }
}
