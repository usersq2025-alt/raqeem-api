<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentUser;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // A1 — تسجيل ولي أمر جديد
    public function register(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:120',
            'email' => 'required|email|unique:parents,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ]);

        $publicId = 'RQMP-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $parent = ParentUser::create([
            'public_id' => $publicId,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $code = (string) random_int(1000, 9999);

        OtpCode::create([
            'parent_id' => $parent->id,
            'code' => $code,
            'purpose' => 'signup_verification',
            'expires_at' => now()->addMinutes(5),
        ]);

        // مؤقتاً بنطبع الرمز بالـ log بدل ما نرسله فعلياً بالبريد (لسا ما ربطنا خدمة بريد)
        \Log::info("OTP for {$parent->email}: {$code}");

        return response()->json([
            'message' => 'تم إنشاء الحساب، تحقق من بريدك',
            'parent_id' => $parent->id,
        ], 201);
    }

    // A2 — تحقق OTP
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|exists:parents,id',
            'code' => 'required|string',
        ]);

        $otp = OtpCode::where('parent_id', $validated['parent_id'])
            ->where('purpose', 'signup_verification')
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if (!$otp || $otp->code !== $validated['code']) {
            return response()->json(['message' => 'رمز غير صحيح'], 422);
        }

        if ($otp->expires_at->isPast()) {
            return response()->json(['message' => 'انتهت صلاحية الرمز'], 422);
        }

        $otp->update(['consumed_at' => now()]);

        $parent = ParentUser::find($validated['parent_id']);
        $parent->update(['email_verified_at' => now()]);

        $token = $parent->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم التحقق بنجاح',
            'token' => $token,
            'parent' => $parent,
        ]);
    }

    // A5 — تسجيل الدخول
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string', // يقبل الإيميل أو RQMP-XXXXXX
            'password' => 'required|string',
        ]);

        $parent = str_starts_with($validated['login'], 'RQMP-')
            ? ParentUser::where('public_id', $validated['login'])->first()
            : ParentUser::where('email', $validated['login'])->first();

        if (!$parent || !Hash::check($validated['password'], $parent->password_hash)) {
            return response()->json(['message' => 'البيانات المدخلة غير صحيحة'], 401);
        }

        $token = $parent->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'parent' => $parent,
        ]);
    }

    // A5-1 — طلب استرجاع كلمة السر: رسالة عامة واحدة دائمًا، بلا الإفصاح عن وجود البريد من عدمه
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $parent = ParentUser::where('email', $validated['email'])->first();

        if ($parent) {
            $code = (string) random_int(1000, 9999);

            OtpCode::create([
                'parent_id' => $parent->id,
                'code' => $code,
                'purpose' => 'password_reset',
                'expires_at' => now()->addMinutes(5),
            ]);

            \Log::info("Password reset OTP for {$parent->email}: {$code}");
        } else {
            // موازنة تقريبية لعدد الاستعلامات بين الحالتين (دفاع أساسي، غير مضمون تمامًا، ضد اكتشاف بريد مسجَّل عبر التوقيت)
            OtpCode::query()->whereRaw('0 = 1')->exists();
        }

        return response()->json([
            'message' => 'إن كان البريد مسجلاً لدينا، ستصلك رسالة تحتوي رمز إعادة تعيين كلمة السر قريبًا',
        ]);
    }

    // A5-1 — تنفيذ إعادة تعيين كلمة السر بالرمز المُرسَل (نفس منطق verifyOtp تمامًا)
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
            'new_password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ]);

        $parent = ParentUser::where('email', $validated['email'])->first();

        $otp = $parent
            ? OtpCode::where('parent_id', $parent->id)
                ->where('purpose', 'password_reset')
                ->whereNull('consumed_at')
                ->latest()
                ->first()
            : null;

        // عدم وجود البريد يُعامَل كرمز خاطئ (رسالة واحدة موحَّدة) لمنع تسريب أي معلومة إضافية بهذه الخطوة أيضًا
        if (!$parent || !$otp || $otp->code !== $validated['code']) {
            return response()->json(['message' => 'رمز غير صحيح'], 422);
        }

        if ($otp->expires_at->isPast()) {
            return response()->json(['message' => 'انتهت صلاحية الرمز'], 422);
        }

        $otp->update(['consumed_at' => now()]);

        $parent->update(['password_hash' => Hash::make($validated['new_password'])]);

        // تسجيل خروج كل الجلسات النشطة الأخرى أمنيًا بعد تغيير كلمة السر
        $parent->tokens()->delete();

        return response()->json(['message' => 'تم تحديث كلمة السر بنجاح']);
    }

    // A5-2 — استرجاع معرّف الحساب (RQMP-XXXXXX): رسالة عامة واحدة دائمًا، بلا OTP (لا يُغيَّر شيء بالحساب)
    public function forgotAccountId(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $parent = ParentUser::where('email', $validated['email'])->first();

        if ($parent) {
            \Log::info("Account ID recovery for {$parent->email}: {$parent->public_id}");
        } else {
            // موازنة تقريبية بنفس فكرة forgotPassword أعلاه
            OtpCode::query()->whereRaw('0 = 1')->exists();
        }

        return response()->json([
            'message' => 'إن كان البريد مسجلاً لدينا، ستصلك رسالة تحتوي معرّف حسابك قريبًا',
        ]);
    }
}