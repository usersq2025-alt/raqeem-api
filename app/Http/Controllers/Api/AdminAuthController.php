<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    // D1 — تسجيل دخول الإدارة (مستقل تمامًا عن مصادقة ولي الأمر)
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = AdminUser::where('email', $validated['email'])->first();

        if (! $admin || ! Hash::check($validated['password'], $admin->password_hash)) {
            return response()->json(['message' => 'البيانات المدخلة غير صحيحة'], 401);
        }

        if ($admin->status !== 'active') {
            return response()->json(['message' => 'الحساب غير مُفعَّل'], 403);
        }

        $admin->update(['last_login_at' => now()]);

        $token = $admin->createToken('admin_auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'admin' => $admin,
        ]);
    }
}
