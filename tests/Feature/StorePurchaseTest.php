<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\ParentUser;
use App\Models\Student;
use App\Models\StoreItem;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StorePurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function makeParentAndToken(): array
    {
        $parent = ParentUser::create([
            'public_id' => 'RQMP-000001',
            'full_name' => 'ولي أمر',
            'email' => 'parent@example.com',
            'password_hash' => Hash::make('Password123'),
            'status' => 'active',
        ]);

        return [$parent, $parent->createToken('t')->plainTextToken];
    }

    public function test_initial_balance_equals_cheapest_active_store_item_price(): void
    {
        [$parent, $token] = $this->makeParentAndToken();
        $grade = Grade::create(['level' => 1, 'name_ar' => 'الأول', 'name_en' => 'Grade 1']);

        StoreItem::create(['category' => 'equipment', 'name' => 'رخيص', 'price_points' => 15, 'is_active' => true]);
        StoreItem::create(['category' => 'equipment', 'name' => 'أرخص', 'price_points' => 5, 'is_active' => true]);
        StoreItem::create(['category' => 'equipment', 'name' => 'غير نشط لكنه أرخص', 'price_points' => 1, 'is_active' => false]);

        $resp = $this->withToken($token)->postJson('/api/students', [
            'public_id' => 'RQMS-000001',
            'full_name' => 'طالب',
            'birth_date' => '2016-01-01',
            'grade_id' => $grade->id,
            'gender' => 'male',
        ])->assertStatus(201);

        $this->assertSame(5, $resp->json('points_balance'));
    }

    public function test_purchase_flow_insufficient_locked_duplicate_and_success(): void
    {
        [$parent, $token] = $this->makeParentAndToken();
        $grade = Grade::create(['level' => 1, 'name_ar' => 'الأول', 'name_en' => 'Grade 1']);
        $subject = Subject::create(['name_ar' => 'مادة', 'name_en' => 'Subject']);
        $unit = Unit::create(['subject_id' => $subject->id, 'grade_id' => $grade->id, 'title' => 'وحدة', 'sort_order' => 1, 'status' => 'published']);

        $student = Student::create([
            'public_id' => 'RQMS-000001',
            'parent_id' => $parent->id,
            'full_name' => 'طالب',
            'birth_date' => '2016-01-01',
            'grade_id' => $grade->id,
            'gender' => 'male',
            'points_balance' => 10,
        ]);

        $openItem = StoreItem::create(['category' => 'equipment', 'name' => 'قلم', 'price_points' => 20, 'is_active' => true, 'unlock_type' => 'open']);
        $affordableItem = StoreItem::create(['category' => 'equipment', 'name' => 'ممحاة', 'price_points' => 7, 'is_active' => true, 'unlock_type' => 'open']);
        $lockedItem = StoreItem::create(['category' => 'furniture', 'name' => 'كرسي مميز', 'price_points' => 1, 'is_active' => true, 'unlock_type' => 'locked_visible', 'unlock_unit_id' => $unit->id]);

        // 1) رصيد غير كافٍ (10 < 20)
        $this->withToken($token)
            ->postJson('/api/student-purchases', ['student_id' => $student->id, 'store_item_id' => $openItem->id])
            ->assertStatus(422);
        $this->assertSame(10, $student->fresh()->points_balance);

        // 2) عنصر محجوب (الوحدة غير مكتملة لهذا الطالب) رغم كفاية الرصيد
        $this->withToken($token)
            ->postJson('/api/student-purchases', ['student_id' => $student->id, 'store_item_id' => $lockedItem->id])
            ->assertStatus(422);
        $this->assertSame(10, $student->fresh()->points_balance);

        // 3) شراء ناجح مع خصم صحيح (10 - 7 = 3)
        $this->withToken($token)
            ->postJson('/api/student-purchases', ['student_id' => $student->id, 'store_item_id' => $affordableItem->id])
            ->assertStatus(201)
            ->assertJsonPath('price_paid', 7);
        $this->assertSame(3, $student->fresh()->points_balance);

        // 4) محاولة شراء نفس العنصر مرة ثانية -> رفض واضح (409) وليس 500، ولا خصم إضافي
        $this->withToken($token)
            ->postJson('/api/student-purchases', ['student_id' => $student->id, 'store_item_id' => $affordableItem->id])
            ->assertStatus(409);
        $this->assertSame(3, $student->fresh()->points_balance);

        $this->assertSame(1, \App\Models\StudentPurchase::where('student_id', $student->id)->count());

        // price_paid لا يُقرأ أبدًا من الطلب حتى لو أُرسل
        $this->withToken($token)
            ->postJson('/api/student-purchases', [
                'student_id' => $student->id,
                'store_item_id' => $openItem->id,
                'price_paid' => 0,
            ])
            ->assertStatus(422); // لا يزال يُرفض لعدم كفاية الرصيد (3 < 20) رغم محاولة تمرير price_paid=0
    }
}
