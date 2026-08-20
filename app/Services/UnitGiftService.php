<?php

namespace App\Services;

use App\Models\PointsTransaction;
use App\Models\Student;
use App\Models\StudentGiftLog;
use App\Models\UnitCompletionReward;
use Illuminate\Support\Facades\DB;

class UnitGiftService
{
    // B8.4 — هدية نهاية الوحدة: تُمنح مرة واحدة فقط لكل (طالب، وحدة) — uq_sgl_student_unit
    // بقاعدة البيانات (المرحلة 1) هو الضامن النهائي، والفحص المسبق هنا يمنع استثناءً خامًا
    public function grantIfEligible(Student $student, int $unitId): void
    {
        $reward = UnitCompletionReward::where('unit_id', $unitId)->first();

        if (! $reward) {
            return; // لا هدية مُعرَّفة لهذه الوحدة أصلًا
        }

        $alreadyGranted = StudentGiftLog::where('student_id', $student->id)
            ->where('unit_id', $unitId)
            ->exists();

        if ($alreadyGranted) {
            return;
        }

        DB::transaction(function () use ($student, $unitId, $reward) {
            StudentGiftLog::create([
                'student_id' => $student->id,
                'unit_id' => $unitId,
                'reward_type' => $reward->reward_type,
                'points_amount' => $reward->points_amount,
                'store_item_id' => $reward->store_item_id,
                'granted_at' => now(),
            ]);

            if ($reward->reward_type === 'points' && $reward->points_amount > 0) {
                PointsTransaction::create([
                    'student_id' => $student->id,
                    'type' => 'gift',
                    'points_change' => $reward->points_amount,
                    'reference_type' => 'unit_completion_rewards',
                    'reference_id' => $reward->id,
                ]);

                Student::where('id', $student->id)->increment('points_balance', $reward->points_amount);
            }
        });
    }
}
