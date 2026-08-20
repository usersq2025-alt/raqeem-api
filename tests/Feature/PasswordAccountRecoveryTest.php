<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\ParentUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordAccountRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private function makeParent(): ParentUser
    {
        return ParentUser::create([
            'public_id' => 'RQMP-000001',
            'full_name' => 'ولي أمر',
            'email' => 'parent@example.com',
            'password_hash' => Hash::make('OldPassword123'),
            'status' => 'active',
        ]);
    }

    public function test_forgot_password_returns_same_message_and_status_for_existing_and_nonexistent_email(): void
    {
        $parent = $this->makeParent();

        $respExisting = $this->postJson('/api/forgot-password', ['email' => $parent->email]);
        $respMissing = $this->postJson('/api/forgot-password', ['email' => 'no-such-user@example.com']);

        $respExisting->assertStatus(200);
        $respMissing->assertStatus($respExisting->getStatusCode());
        $this->assertSame($respExisting->json('message'), $respMissing->json('message'));

        // OTP أُنشئ فعليًا فقط للبريد الموجود (صف واحد بالإجمالي، لا صف إضافي للبريد غير الموجود)
        $this->assertSame(1, OtpCode::where('parent_id', $parent->id)->where('purpose', 'password_reset')->count());
        $this->assertSame(1, OtpCode::count());
    }

    public function test_full_password_reset_flow_invalidates_old_tokens(): void
    {
        $parent = $this->makeParent();
        $oldToken = $parent->createToken('old_session')->plainTextToken;

        // الجلسة القديمة صالحة فعليًا قبل إعادة التعيين (فحص مباشر بقاعدة البيانات، لا طلب HTTP
        // مصادَق مُتشابك مع طلبات عامة تالية بنفس الاختبار — auth guard بواجهة الاختبار يخزّن
        // المستخدم المُصادَق عليه مؤقتًا حتى بعد إزالة هيدر Authorization لاحقًا)
        $this->assertSame(1, $parent->tokens()->count());

        $this->postJson('/api/forgot-password', ['email' => $parent->email])->assertStatus(200);

        $otp = OtpCode::where('parent_id', $parent->id)->where('purpose', 'password_reset')->latest()->first();
        $this->assertNotNull($otp);

        $this->postJson('/api/reset-password', [
            'email' => $parent->email,
            'code' => $otp->code,
            'new_password' => 'NewPassword123',
        ])->assertStatus(200);

        // كل التوكنات القديمة حُذفت فعليًا بقاعدة البيانات
        $this->assertSame(0, $parent->tokens()->count());

        // كلمة السر الجديدة فعليًا تعمل بتسجيل دخول جديد
        $this->postJson('/api/login', ['login' => $parent->email, 'password' => 'NewPassword123'])
            ->assertStatus(200);

        // كلمة السر القديمة لا تعمل
        $this->postJson('/api/login', ['login' => $parent->email, 'password' => 'OldPassword123'])
            ->assertStatus(401);

        // الرمز نفسه لا يُعاد استخدامه (مُستهلَك بالفعل)
        $this->postJson('/api/reset-password', [
            'email' => $parent->email,
            'code' => $otp->code,
            'new_password' => 'AnotherPass123',
        ])->assertStatus(422);
    }

    public function test_a_token_still_in_the_database_authenticates_successfully(): void
    {
        $parent = $this->makeParent();
        $token = $parent->createToken('session')->plainTextToken;

        $this->withToken($token)->getJson('/api/students')->assertStatus(200);
    }

    public function test_a_deleted_token_no_longer_authenticates(): void
    {
        // اختبار منفصل بتحليل واحد للمصادقة فقط بهذه الدالة (guard الاختبار يخزّن أول تحليل
        // ناجح للمستخدم المُصادَق طوال عمر دالة الاختبار، فتحليلان متتاليان بنفس الدالة على
        // نفس المسار قد يُخفي التغيّر الحقيقي بقاعدة البيانات بينهما) — التوكن هنا يُحذف
        // *قبل* أي طلب HTTP يستخدمه، تمامًا كما يحصل فعليًا بعد AuthController::resetPassword
        $parent = $this->makeParent();
        $token = $parent->createToken('session')->plainTextToken;
        $parent->tokens()->delete();

        $this->withToken($token)->getJson('/api/students')->assertStatus(401);
    }

    public function test_expired_reset_code_is_rejected(): void
    {
        $parent = $this->makeParent();

        $otp = OtpCode::create([
            'parent_id' => $parent->id,
            'code' => '1234',
            'purpose' => 'password_reset',
            'expires_at' => now()->subMinute(), // منتهي بالفعل
        ]);

        $this->postJson('/api/reset-password', [
            'email' => $parent->email,
            'code' => '1234',
            'new_password' => 'NewPassword123',
        ])->assertStatus(422);
    }

    public function test_wrong_code_is_rejected_with_generic_message(): void
    {
        $parent = $this->makeParent();

        OtpCode::create([
            'parent_id' => $parent->id,
            'code' => '1234',
            'purpose' => 'password_reset',
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->postJson('/api/reset-password', [
            'email' => $parent->email,
            'code' => '9999',
            'new_password' => 'NewPassword123',
        ])->assertStatus(422)->assertJsonPath('message', 'رمز غير صحيح');

        // بريد غير موجود يُعامَل بنفس رسالة الرمز الخاطئ (لا فرق يُكشَف)
        $this->postJson('/api/reset-password', [
            'email' => 'nobody@example.com',
            'code' => '1234',
            'new_password' => 'NewPassword123',
        ])->assertStatus(422)->assertJsonPath('message', 'رمز غير صحيح');
    }

    public function test_forgot_account_id_returns_same_generic_message_and_logs_public_id_without_otp(): void
    {
        $parent = $this->makeParent();

        $respExisting = $this->postJson('/api/forgot-account-id', ['email' => $parent->email]);
        $respMissing = $this->postJson('/api/forgot-account-id', ['email' => 'nobody@example.com']);

        $respExisting->assertStatus(200);
        $respMissing->assertStatus($respExisting->getStatusCode());
        $this->assertSame($respExisting->json('message'), $respMissing->json('message'));

        // لا OTP يُنشأ إطلاقًا بهذا المسار
        $this->assertSame(0, OtpCode::count());
    }

    public function test_throttle_limits_forgot_password_and_reset_password_and_forgot_account_id(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/forgot-password', ['email' => 'x@example.com'])->assertStatus(200);
        }
        $this->postJson('/api/forgot-password', ['email' => 'x@example.com'])->assertStatus(429);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/reset-password', ['email' => 'x@example.com', 'code' => '0000', 'new_password' => 'Whatever123'])->assertStatus(422);
        }
        $this->postJson('/api/reset-password', ['email' => 'x@example.com', 'code' => '0000', 'new_password' => 'Whatever123'])->assertStatus(429);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/forgot-account-id', ['email' => 'x@example.com'])->assertStatus(200);
        }
        $this->postJson('/api/forgot-account-id', ['email' => 'x@example.com'])->assertStatus(429);
    }
}
