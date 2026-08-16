<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentBadge;
use Illuminate\Http\Request;

class StudentBadgeController extends Controller
{
    // GET /api/student-badges
    public function index(Request $request)
    {
        $query = StudentBadge::query();

        // دعم بسيط للـ pagination: /api/student-badges?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/student-badges/{id}
    public function show(StudentBadge $studentBadge)
    {
        return response()->json($studentBadge);
    }

    // POST /api/student-badges
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'badge_id' => 'sometimes',
            'earned_at' => 'sometimes',
        ]);

        $studentBadge = StudentBadge::create($validated);

        return response()->json($studentBadge, 201);
    }

    // PUT/PATCH /api/student-badges/{id}
    public function update(Request $request, StudentBadge $studentBadge)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'badge_id' => 'sometimes',
            'earned_at' => 'sometimes',
        ]);

        $studentBadge->update($validated);

        return response()->json($studentBadge);
    }

    // DELETE /api/student-badges/{id}
    public function destroy(StudentBadge $studentBadge)
    {
        $studentBadge->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
