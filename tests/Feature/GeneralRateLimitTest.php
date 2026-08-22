<?php

namespace Tests\Feature;

use App\Models\ParentUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class GeneralRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private function makeParentToken(string $email): string
    {
        $parent = ParentUser::create([
            'public_id' => 'RQMP-'.random_int(100000, 999999),
            'full_name' => 'ولي أمر',
            'email' => $email,
            'password_hash' => Hash::make('Password123'),
            'status' => 'active',
        ]);

        return $parent->createToken('t')->plainTextToken;
    }

    public function test_authenticated_user_is_throttled_at_60_per_minute_on_an_ordinary_route(): void
    {
        $token = $this->makeParentToken('user1@example.com');

        for ($i = 0; $i < 60; $i++) {
            $this->withToken($token)->getJson('/api/subjects')->assertStatus(200);
        }

        $resp = $this->withToken($token)->getJson('/api/subjects');
        $resp->assertStatus(429);
        $this->assertNotNull($resp->json('retry_after_seconds'));
        $this->assertIsInt($resp->json('retry_after_seconds'));
        $this->assertNotNull($resp->json('message'));
    }

    public function test_a_different_authenticated_user_is_unaffected_by_the_first_users_limit(): void
    {
        // ملاحظة منهجية: لا نُنشئ استنفاد حد المستخدم A عبر 60 طلب HTTP فعلي بنفس دالة
        // الاختبار التي تستخدم لاحقًا توكن B — auth guard بواجهة الاختبار يخزّن أول
        // مستخدم يُحلَّل طوال عمر دالة الاختبار فلا يُعاد تحليله عند تبديل التوكن لاحقًا
        // بنفس الدالة (قيد بيئة الاختبار فقط، لا يحدث بالإنتاج الحقيقي إطلاقًا حيث كل
        // طلب عملية مستقلة تمامًا). لذا نُحاكي استنفاد حد A مباشرة عبر مفتاح RateLimiter
        // نفسه (بنفس صيغة AppServiceProvider::configureRateLimiting)، ثم نتحقق فعليًا
        // بطلب HTTP حقيقي واحد بتوكن B أنه غير متأثر إطلاقًا.
        $userA = ParentUser::create([
            'public_id' => 'RQMP-'.random_int(100000, 999999),
            'full_name' => 'ولي أمر A',
            'email' => 'userA@example.com',
            'password_hash' => Hash::make('Password123'),
            'status' => 'active',
        ]);
        $tokenB = $this->makeParentToken('userB@example.com');

        $keyA = 'api-general:user:'.get_class($userA).':'.$userA->getKey();
        for ($i = 0; $i < 60; $i++) {
            RateLimiter::hit($keyA, 60);
        }
        $this->assertTrue(RateLimiter::tooManyAttempts($keyA, 60));

        $this->withToken($tokenB)->getJson('/api/subjects')->assertStatus(200);
    }

    public function test_verify_otp_still_throttles_at_its_own_narrower_limit_unaffected_by_general_limiter(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/verify-otp', ['parent_id' => 1, 'code' => '0000'])->assertStatus(422);
        }

        $this->postJson('/api/verify-otp', ['parent_id' => 1, 'code' => '0000'])->assertStatus(429);
    }
}
