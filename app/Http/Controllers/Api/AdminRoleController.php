<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    // GET /api/admin-roles
    public function index(Request $request)
    {
        $query = AdminRole::query();

        // دعم بسيط للـ pagination: /api/admin-roles?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/admin-roles/{id}
    public function show(AdminRole $adminRole)
    {
        return response()->json($adminRole);
    }

    // POST /api/admin-roles
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'sometimes',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
        ]);

        $adminRole = AdminRole::create($validated);

        return response()->json($adminRole, 201);
    }

    // PUT/PATCH /api/admin-roles/{id}
    public function update(Request $request, AdminRole $adminRole)
    {
        $validated = $request->validate([
            'code' => 'sometimes',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
        ]);

        $adminRole->update($validated);

        return response()->json($adminRole);
    }

    // DELETE /api/admin-roles/{id}
    public function destroy(AdminRole $adminRole)
    {
        $adminRole->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
