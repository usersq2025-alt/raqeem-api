<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentAnswer;
use App\Models\StudentLessonAttempt;
use Illuminate\Http\Request;

class StudentAnswerController extends Controller
{
    // GET /api/student-answers
    public function index(Request $request)
    {
        $this->authorize('viewAny', StudentAnswer::class);

        $query = StudentAnswer::whereHas(
            'attempt.student',
            fn ($q) => $q->where('parent_id', $request->user()->id)
        );

        // دعم بسيط للـ pagination: /api/student-answers?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/student-answers/{id}
    public function show(StudentAnswer $studentAnswer)
    {
        $this->authorize('view', $studentAnswer);

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

        // student_answers لا يملك student_id مباشرة، بل عبر attempt_id -> student_lesson_attempts.student_id
        $studentId = isset($validated['attempt_id'])
            ? StudentLessonAttempt::find($validated['attempt_id'])?->student_id
            : null;

        $this->authorize('create', [StudentAnswer::class, $studentId]);

        $studentAnswer = StudentAnswer::create($validated);

        return response()->json($studentAnswer, 201);
    }

    // PUT/PATCH /api/student-answers/{id}
    public function update(Request $request, StudentAnswer $studentAnswer)
    {
        $this->authorize('update', $studentAnswer);

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
        $this->authorize('delete', $studentAnswer);

        $studentAnswer->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
