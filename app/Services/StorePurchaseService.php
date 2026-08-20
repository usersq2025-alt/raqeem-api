<?php

namespace App\Services;

use App\Exceptions\Store\AlreadyPurchasedException;
use App\Exceptions\Store\InsufficientBalanceException;
use App\Exceptions\Store\ItemLockedException;
use App\Exceptions\Store\ItemNotAvailableException;
use App\Models\Student;
use App\Models\StudentPurchase;
use App\Models\StoreItem;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class StorePurchaseService
{
    public function __construct(private UnitCompletionChecker $unitCompletionChecker)
    {
    }

    // C2 — شراء ذرّي: خصم الرصيد + إنشاء سجل الشراء معًا، أو لا شيء عند الفشل
    public function purchase(Student $student, StoreItem $item): StudentPurchase
    {
        if (! $item->is_active) {
            throw new ItemNotAvailableException();
        }

        if ($item->unlock_type !== 'open' && ! $this->isUnlockedFor($student, $item)) {
            throw new ItemLockedException();
        }

        if (StudentPurchase::where('student_id', $student->id)->where('store_item_id', $item->id)->exists()) {
            throw new AlreadyPurchasedException();
        }

        if ($student->points_balance < $item->price_points) {
            throw new InsufficientBalanceException();
        }

        return DB::transaction(function () use ($student, $item) {
            // شرط points_balance ضمن الـ UPDATE نفسه يمنع أي سباق يُنقص الرصيد لأقل من الصفر
            $affected = Student::where('id', $student->id)
                ->where('points_balance', '>=', $item->price_points)
                ->decrement('points_balance', $item->price_points);

            if (! $affected) {
                throw new InsufficientBalanceException();
            }

            try {
                return StudentPurchase::create([
                    'student_id' => $student->id,
                    'store_item_id' => $item->id,
                    'price_paid' => $item->price_points,
                    'purchased_at' => now(),
                ]);
            } catch (QueryException $e) {
                // شبكة أمان لسباق نادر بين الفحص المسبق والإدراج الفعلي — يمنعه فعليًا
                // uq_sp_student_item بقاعدة البيانات (أُنشئ بالمرحلة 1)، هذا فقط يحوّل
                // الخطأ الخام إلى رسالة واضحة بدل 500، والـ transaction تتراجع بالكامل
                throw new AlreadyPurchasedException();
            }
        });
    }

    private function isUnlockedFor(Student $student, StoreItem $item): bool
    {
        if (! $item->unlock_unit_id) {
            return false;
        }

        return $this->unitCompletionChecker->hasCompletedUnit($student, $item->unlock_unit_id);
    }
}
