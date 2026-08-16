<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    // GET /api/badges
    public function index(Request $request)
    {
        $query = Badge::query();

        // دعم بسيط للـ pagination: /api/badges?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/badges/{id}
    public function show(Badge $badge)
    {
        return response()->json($badge);
    }

    // POST /api/badges
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'sometimes',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'threshold_type' => 'sometimes',
            'threshold_value' => 'sometimes',
            'icon_url' => 'sometimes',
        ]);

        $badge = Badge::create($validated);

        return response()->json($badge, 201);
    }

    // PUT/PATCH /api/badges/{id}
    public function update(Request $request, Badge $badge)
    {
        $validated = $request->validate([
            'code' => 'sometimes',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'threshold_type' => 'sometimes',
            'threshold_value' => 'sometimes',
            'icon_url' => 'sometimes',
        ]);

        $badge->update($validated);

        return response()->json($badge);
    }

    // DELETE /api/badges/{id}
    public function destroy(Badge $badge)
    {
        $badge->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
