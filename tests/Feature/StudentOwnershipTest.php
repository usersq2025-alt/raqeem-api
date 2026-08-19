<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\ParentUser;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_cannot_read_or_update_another_parents_student(): void
    {
        $grade = Grade::create([
            'level' => 1,
            'name_ar' => 'الصف الأول',
            'name_en' => 'Grade 1',
        ]);

        $parentA = ParentUser::create([
            'public_id' => 'RQMP-000001',
            'full_name' => 'Parent A',
            'email' => 'parent-a@example.com',
            'password_hash' => Hash::make('Password123'),
            'status' => 'active',
        ]);

        $parentB = ParentUser::create([
            'public_id' => 'RQMP-000002',
            'full_name' => 'Parent B',
            'email' => 'parent-b@example.com',
            'password_hash' => Hash::make('Password123'),
            'status' => 'active',
        ]);

        $childOfA = Student::create([
            'public_id' => 'RQMS-000001',
            'parent_id' => $parentA->id,
            'full_name' => 'Child A',
            'birth_date' => '2015-01-01',
            'grade_id' => $grade->id,
            'gender' => 'male',
        ]);

        $childOfB = Student::create([
            'public_id' => 'RQMS-000002',
            'parent_id' => $parentB->id,
            'full_name' => 'Child B',
            'birth_date' => '2015-06-01',
            'grade_id' => $grade->id,
            'gender' => 'female',
        ]);

        $tokenA = $parentA->createToken('test')->plainTextToken;

        // ولي الأمر A يرى طفله الخاص بلا مشكلة
        $this->withToken($tokenA)
            ->getJson("/api/students/{$childOfA->id}")
            ->assertStatus(200)
            ->assertJsonPath('id', $childOfA->id);

        // ولي الأمر A لا يستطيع قراءة طفل ولي الأمر B (IDOR)
        $this->withToken($tokenA)
            ->getJson("/api/students/{$childOfB->id}")
            ->assertStatus(403);

        // ولا تعديله
        $this->withToken($tokenA)
            ->patchJson("/api/students/{$childOfB->id}", ['full_name' => 'Hacked'])
            ->assertStatus(403);

        // ولا حذفه
        $this->withToken($tokenA)
            ->deleteJson("/api/students/{$childOfB->id}")
            ->assertStatus(403);

        // القائمة /api/students تُرجع فقط أطفال ولي الأمر A
        $this->withToken($tokenA)
            ->getJson('/api/students')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $childOfA->id);

        // تأكيد أن الطفل B سليم ولم يتأثر بمحاولة التعديل المرفوضة
        $this->assertSame('Child B', $childOfB->fresh()->full_name);
    }
}
