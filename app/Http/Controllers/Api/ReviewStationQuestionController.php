<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReviewStationQuestion;
use Illuminate\Http\Request;

class ReviewStationQuestionController extends Controller
{
    // GET /api/review-station-questions
    public function index(Request $request)
    {
        $query = ReviewStationQuestion::query();

        // دعم بسيط للـ pagination: /api/review-station-questions?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/review-station-questions/{id}
    public function show(ReviewStationQuestion $reviewStationQuestion)
    {
        return response()->json($reviewStationQuestion);
    }

    // POST /api/review-station-questions
    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'sometimes',
            'question_id' => 'sometimes',
            'is_correct' => 'sometimes',
            'answered_at' => 'sometimes',
        ]);

        $reviewStationQuestion = ReviewStationQuestion::create($validated);

        return response()->json($reviewStationQuestion, 201);
    }

    // PUT/PATCH /api/review-station-questions/{id}
    public function update(Request $request, ReviewStationQuestion $reviewStationQuestion)
    {
        $validated = $request->validate([
            'session_id' => 'sometimes',
            'question_id' => 'sometimes',
            'is_correct' => 'sometimes',
            'answered_at' => 'sometimes',
        ]);

        $reviewStationQuestion->update($validated);

        return response()->json($reviewStationQuestion);
    }

    // DELETE /api/review-station-questions/{id}
    public function destroy(ReviewStationQuestion $reviewStationQuestion)
    {
        $reviewStationQuestion->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
