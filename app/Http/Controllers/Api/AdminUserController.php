<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    // GET /api/admin-users
    public function index(Request $request)
    {
        $query = AdminUser::query();

        // دعم بسيط للـ pagination: /api/admin-users?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/admin-users/{id}
    public function show(AdminUser $adminUser)
    {
        return response()->json($adminUser);
    }

    // POST /api/admin-users
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes',
            'email' => 'sometimes',
            'password_hash' => 'sometimes',
            'role_id' => 'sometimes',
            'status' => 'sometimes',
            'last_login_at' => 'sometimes',
            'invited_at' => 'sometimes',
        ]);

        $adminUser = AdminUser::create($validated);

        return response()->json($adminUser, 201);
    }

    // PUT/PATCH /api/admin-users/{id}
    public function update(Request $request, AdminUser $adminUser)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes',
            'email' => 'sometimes',
            'password_hash' => 'sometimes',
            'role_id' => 'sometimes',
            'status' => 'sometimes',
            'last_login_at' => 'sometimes',
            'invited_at' => 'sometimes',
        ]);

        $adminUser->update($validated);

        return response()->json($adminUser);
    }

    // DELETE /api/admin-users/{id}
    public function destroy(AdminUser $adminUser)
    {
        $adminUser->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
