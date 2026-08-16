<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // GET /api/students
    public function index(Request $request)
    {
        $query = Student::query();

        // دعم بسيط للـ pagination: /api/students?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/students/{id}
    public function show(Student $student)
    {
        return response()->json($student);
    }

    // POST /api/students
    public function store(Request $request)
    {
        $validated = $request->validate([
            'public_id' => 'sometimes',
            'parent_id' => 'sometimes',
            'full_name' => 'sometimes',
            'birth_date' => 'sometimes',
            'grade_id' => 'sometimes',
            'gender' => 'sometimes',
            'profession_id' => 'sometimes',
            'points_balance' => 'sometimes',
            'streak_current' => 'sometimes',
            'streak_longest' => 'sometimes',
            'last_activity_date' => 'sometimes',
            'status' => 'sometimes',
        ]);

        $student = Student::create($validated);

        return response()->json($student, 201);
    }

    // PUT/PATCH /api/students/{id}
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'public_id' => 'sometimes',
            'parent_id' => 'sometimes',
            'full_name' => 'sometimes',
            'birth_date' => 'sometimes',
            'grade_id' => 'sometimes',
            'gender' => 'sometimes',
            'profession_id' => 'sometimes',
            'points_balance' => 'sometimes',
            'streak_current' => 'sometimes',
            'streak_longest' => 'sometimes',
            'last_activity_date' => 'sometimes',
            'status' => 'sometimes',
        ]);

        $student->update($validated);

        return response()->json($student);
    }

    // DELETE /api/students/{id}
    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
