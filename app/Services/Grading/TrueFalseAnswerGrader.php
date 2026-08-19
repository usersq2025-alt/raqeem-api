<?php

namespace App\Services\Grading;

class TrueFalseAnswerGrader implements AnswerGrader
{
    /**
     * الشكل المعتمَد لأول مرة لنمط "صح أو خطأ":
     *
     * payload:          {"correct_answer": true}
     * selected_answer:  {"answer": true}
     */
    public function isCorrect(array $payload, mixed $selectedAnswer): bool
    {
        if (! array_key_exists('correct_answer', $payload)) {
            return false;
        }

        $selected = is_array($selectedAnswer) ? ($selectedAnswer['answer'] ?? null) : null;

        if (! is_bool($selected)) {
            return false;
        }

        return $payload['correct_answer'] === $selected;
    }
}
