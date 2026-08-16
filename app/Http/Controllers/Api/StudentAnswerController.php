<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;

class StudentAnswerController extends Controller
{
    // GET /api/student-answers
    public function index(Request $request)
    {
        $query = StudentAnswer::query();

        // دعم بسيط للـ pagination: /api/student-answers?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/student-answers/{id}
    public function show(StudentAnswer $studentAnswer)
    {
        return response()->json($studentAnswer);
    }

    // POST /api/student-answers
    public function store(Request $request)
    {
        $validated = $request->validate([
            'attempt_id' => 'sometimes',
            'game_id' => 'sometimes',
            'question_id' => 'sometimes',
            'is_correct' => 'sometimes',
            'selected_answer' => 'sometimes',
            'answered_at' => 'sometimes',
        ]);

        $studentAnswer = StudentAnswer::create($validated);

        return response()->json($studentAnswer, 201);
    }

    // PUT/PATCH /api/student-answers/{id}
    public function update(Request $request, StudentAnswer $studentAnswer)
    {
        $validated = $request->validate([
            'attempt_id' => 'sometimes',
            'game_id' => 'sometimes',
            'question_id' => 'sometimes',
            'is_correct' => 'sometimes',
            'selected_answer' => 'sometimes',
            'answered_at' => 'sometimes',
        ]);

        $studentAnswer->update($validated);

        return response()->json($studentAnswer);
    }

    // DELETE /api/student-answers/{id}
    public function destroy(StudentAnswer $studentAnswer)
    {
        $studentAnswer->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
