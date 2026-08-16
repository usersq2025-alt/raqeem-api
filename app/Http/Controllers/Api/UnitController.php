<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    // GET /api/units
    public function index(Request $request)
    {
        $query = Unit::query();

        // دعم بسيط للـ pagination: /api/units?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/units/{id}
    public function show(Unit $unit)
    {
        return response()->json($unit);
    }

    // POST /api/units
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'sometimes',
            'grade_id' => 'sometimes',
            'title' => 'sometimes',
            'cover_media_id' => 'sometimes',
            'sort_order' => 'sometimes',
            'status' => 'sometimes',
        ]);

        $unit = Unit::create($validated);

        return response()->json($unit, 201);
    }

    // PUT/PATCH /api/units/{id}
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'subject_id' => 'sometimes',
            'grade_id' => 'sometimes',
            'title' => 'sometimes',
            'cover_media_id' => 'sometimes',
            'sort_order' => 'sometimes',
            'status' => 'sometimes',
        ]);

        $unit->update($validated);

        return response()->json($unit);
    }

    // DELETE /api/units/{id}
    public function destroy(Unit $unit)
    {
        $unit->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
