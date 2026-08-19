<?php

namespace App\Services\Grading;

class AnswerGraderFactory
{
    /**
     * دعم كامل لِـ mcq وtrue_false أولوية بهذه المرحلة (حسب الطلب). إضافة نمط
     * جديد لاحقًا يعني فقط: كتابة كلاس Grader جديد يُنفِّذ AnswerGrader، وإضافة
     * سطر واحد هنا — لا حاجة لتعديل أي كود آخر بمنطق التصحيح.
     */
    private const MAP = [
        'mcq' => McqAnswerGrader::class,
        'true_false' => TrueFalseAnswerGrader::class,
    ];

    public static function forGameTypeCode(string $gameTypeCode): AnswerGrader
    {
        $class = self::MAP[$gameTypeCode] ?? null;

        if (! $class) {
            throw new UnsupportedGameTypeException($gameTypeCode);
        }

        return new $class;
    }
}
