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
        $query = ParentUser::query();

        // دعم بسيط للـ pagination: /api/parents?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/parents/{id}
    public function show(ParentUser $parentUser)
    {
        return response()->json($parentUser);
    }

    // POST /api/parents
    public function store(Request $request)
    {
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
        $parentUser->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
