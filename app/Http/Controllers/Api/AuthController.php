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
}