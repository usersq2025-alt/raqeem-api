<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReviewStationSession;
use App\Services\ReviewStationService;
use Illuminate\Http\Request;

class ReviewStationController extends Controller
{
    public function __construct(private ReviewStationService $service)
    {
    }

    // POST /api/review-sessions/{session}/answer
    public function answer(Request $request, ReviewStationSession $session)
    {
        $this->authorize('update', $session);

        $validated = $request->validate([
            'question_id' => 'required|integer',
            'selected_answer' => 'required',
        ]);

        $reviewQuestion = $this->service->submitAnswer($session, $validated['question_id'], $validated['selected_answer']);

        return response()->json([
            'is_correct' => (bool) $reviewQuestion->is_correct,
            'session' => $session->fresh(),
        ], 201);
    }
}
