<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentUser;
use Illuminate\Http\Request;

class ParentUserController extends Controller
{
    // GET /api/parents
    public function index(Request $request)
    {
        $this->authorize('viewAny', ParentUser::class);

        // كل ولي أمر يرى بياناته الشخصية فقط، لا قائمة كل أولياء الأمور
        $query = ParentUser::where('id', $request->user()->id);

        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/parents/{id}
    public function show(ParentUser $parentUser)
    {
        $this->authorize('view', $parentUser);

        return response()->json($parentUser);
    }

    // POST /api/parents
    public function store(Request $request)
    {
        // إنشاء حساب ولي أمر جديد يتم فقط عبر POST /register بدون توكن (انظر ParentUserPolicy::create)
        $this->authorize('create', ParentUser::class);

        $validated = $request->validate([
            'public_id' => 'sometimes',
            'full_name' => 'sometimes',
            'email' => 'sometimes',
            'phone_country_code' => 'sometimes',
            'phone' => 'sometimes',
            'password_hash' => 'sometimes',
            'email_verified_at' => 'sometimes',
            'status' => 'sometimes',
        ]);

        $parentUser = ParentUser::create($validated);

        return response()->json($parentUser, 201);
    }

    // PUT/PATCH /api/parents/{id}
    public function update(Request $request, ParentUser $parentUser)
    {
        $this->authorize('update', $parentUser);

        $validated = $request->validate([
            'public_id' => 'sometimes',
            'full_name' => 'sometimes',
            'email' => 'sometimes',
            'phone_country_code' => 'sometimes',
            'phone' => 'sometimes',
            'password_hash' => 'sometimes',
            'email_verified_at' => 'sometimes',
            'status' => 'sometimes',
        ]);

        $parentUser->update($validated);

        return response()->json($parentUser);
    }

    // DELETE /api/parents/{id}
    public function destroy(ParentUser $parentUser)
    {
        $this->authorize('delete', $parentUser);

        $parentUser->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
