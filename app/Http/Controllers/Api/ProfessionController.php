<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profession;
use Illuminate\Http\Request;

class ProfessionController extends Controller
{
    // GET /api/professions
    public function index(Request $request)
    {
        $query = Profession::query();

        // دعم بسيط للـ pagination: /api/professions?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/professions/{id}
    public function show(Profession $profession)
    {
        return response()->json($profession);
    }

    // POST /api/professions
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'sometimes',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'avatar_asset_url' => 'sometimes',
            'sort_order' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $profession = Profession::create($validated);

        return response()->json($profession, 201);
    }

    // PUT/PATCH /api/professions/{id}
    public function update(Request $request, Profession $profession)
    {
        $validated = $request->validate([
            'code' => 'sometimes',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'avatar_asset_url' => 'sometimes',
            'sort_order' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $profession->update($validated);

        return response()->json($profession);
    }

    // DELETE /api/professions/{id}
    public function destroy(Profession $profession)
    {
        $profession->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
