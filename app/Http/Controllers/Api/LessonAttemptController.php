<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentLessonAttempt;
use App\Services\LessonAttemptService;
use Illuminate\Http\Request;

class LessonAttemptController extends Controller
{
    public function __construct(private LessonAttemptService $service)
    {
    }

    // POST /api/lessons/{lesson}/attempts/start
    public function start(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer',
        ]);

        $this->authorize('create', [StudentLessonAttempt::class, $validated['student_id']]);

        $student = Student::findOrFail($validated['student_id']);

        $attempt = $this->service->startOrResume($student, $lesson);

        return response()->json($this->formatAttempt($attempt));
    }

    // POST /api/attempts/{attempt}/answer
    public function answer(Request $request, StudentLessonAttempt $attempt)
    {
        $this->authorize('update', $attempt);

        $validated = $request->validate([
            'question_id' => 'required|integer',
            'selected_answer' => 'required',
        ]);

        $answer = $this->service->submitAnswer($attempt, $validated['question_id'], $validated['selected_answer']);

        return response()->json([
            'is_correct' => (bool) $answer->is_correct,
            'attempt' => $this->formatAttempt($attempt->fresh()),
        ], 201);
    }

    // POST /api/attempts/{attempt}/complete
    public function complete(StudentLessonAttempt $attempt)
    {
        $this->authorize('update', $attempt);

        $attempt = $this->service->complete($attempt);

        return response()->json($this->formatAttempt($attempt));
    }

    private function formatAttempt(StudentLessonAttempt $attempt): array
    {
        $totalQuestions = $this->service->totalQuestionsForLesson($attempt->lesson);

        return [
            'id' => $attempt->id,
            'student_id' => $attempt->student_id,
            'lesson_id' => $attempt->lesson_id,
            'attempt_number' => $attempt->attempt_number,
            'status' => $attempt->status,
            'correct_count' => $attempt->correct_count,
            'wrong_count' => $attempt->wrong_count,
            'total_questions' => $totalQuestions,
            'battery_segments_remaining' => $this->service->batterySegmentsRemaining($attempt, $totalQuestions),
            'current_game_id' => $attempt->current_game_id,
            'current_question_id' => $attempt->current_question_id,
            'recharge_ends_at' => $attempt->recharge_ends_at?->toIso8601String(),
            'stars' => $attempt->stars,
            'points_earned' => $attempt->points_earned,
            'started_at' => $attempt->started_at?->toIso8601String(),
            'completed_at' => $attempt->completed_at?->toIso8601String(),
        ];
    }
}
