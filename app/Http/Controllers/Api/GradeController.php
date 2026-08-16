<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    // GET /api/grades
    public function index(Request $request)
    {
        $query = Grade::query();

        // دعم بسيط للـ pagination: /api/grades?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/grades/{id}
    public function show(Grade $grade)
    {
        return response()->json($grade);
    }

    // POST /api/grades
    public function store(Request $request)
    {
        $validated = $request->validate([
            'level' => 'sometimes',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $grade = Grade::create($validated);

        return response()->json($grade, 201);
    }

    // PUT/PATCH /api/grades/{id}
    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'level' => 'sometimes',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $grade->update($validated);

        return response()->json($grade);
    }

    // DELETE /api/grades/{id}
    public function destroy(Grade $grade)
    {
        $grade->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
