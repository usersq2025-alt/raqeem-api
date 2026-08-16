<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use Illuminate\Http\Request;

class OtpCodeController extends Controller
{
    // GET /api/otp-codes
    public function index(Request $request)
    {
        $query = OtpCode::query();

        // دعم بسيط للـ pagination: /api/otp-codes?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/otp-codes/{id}
    public function show(OtpCode $otpCode)
    {
        return response()->json($otpCode);
    }

    // POST /api/otp-codes
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'sometimes',
            'code' => 'sometimes',
            'purpose' => 'sometimes',
            'expires_at' => 'sometimes',
            'consumed_at' => 'sometimes',
        ]);

        $otpCode = OtpCode::create($validated);

        return response()->json($otpCode, 201);
    }

    // PUT/PATCH /api/otp-codes/{id}
    public function update(Request $request, OtpCode $otpCode)
    {
        $validated = $request->validate([
            'parent_id' => 'sometimes',
            'code' => 'sometimes',
            'purpose' => 'sometimes',
            'expires_at' => 'sometimes',
            'consumed_at' => 'sometimes',
        ]);

        $otpCode->update($validated);

        return response()->json($otpCode);
    }

    // DELETE /api/otp-codes/{id}
    public function destroy(OtpCode $otpCode)
    {
        $otpCode->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
