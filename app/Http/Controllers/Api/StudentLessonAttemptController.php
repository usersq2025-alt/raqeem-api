<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentLessonAttempt;
use Illuminate\Http\Request;

class StudentLessonAttemptController extends Controller
{
    // GET /api/student-lesson-attempts
    public function index(Request $request)
    {
        $query = StudentLessonAttempt::query();

        // دعم بسيط للـ pagination: /api/student-lesson-attempts?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/student-lesson-attempts/{id}
    public function show(StudentLessonAttempt $studentLessonAttempt)
    {
        return response()->json($studentLessonAttempt);
    }

    // POST /api/student-lesson-attempts
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'lesson_id' => 'sometimes',
            'attempt_number' => 'sometimes',
            'status' => 'sometimes',
            'correct_count' => 'sometimes',
            'wrong_count' => 'sometimes',
            'stars' => 'sometimes',
            'points_earned' => 'sometimes',
            'current_game_id' => 'sometimes',
            'current_question_id' => 'sometimes',
            'recharge_ends_at' => 'sometimes',
            'started_at' => 'sometimes',
            'completed_at' => 'sometimes',
        ]);

        $studentLessonAttempt = StudentLessonAttempt::create($validated);

        return response()->json($studentLessonAttempt, 201);
    }

    // PUT/PATCH /api/student-lesson-attempts/{id}
    public function update(Request $request, StudentLessonAttempt $studentLessonAttempt)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'lesson_id' => 'sometimes',
            'attempt_number' => 'sometimes',
            'status' => 'sometimes',
            'correct_count' => 'sometimes',
            'wrong_count' => 'sometimes',
            'stars' => 'sometimes',
            'points_earned' => 'sometimes',
            'current_game_id' => 'sometimes',
            'current_question_id' => 'sometimes',
            'recharge_ends_at' => 'sometimes',
            'started_at' => 'sometimes',
            'completed_at' => 'sometimes',
        ]);

        $studentLessonAttempt->update($validated);

        return response()->json($studentLessonAttempt);
    }

    // DELETE /api/student-lesson-attempts/{id}
    public function destroy(StudentLessonAttempt $studentLessonAttempt)
    {
        $studentLessonAttempt->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
