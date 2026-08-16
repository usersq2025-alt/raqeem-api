<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReviewStationSession;
use Illuminate\Http\Request;

class ReviewStationSessionController extends Controller
{
    // GET /api/review-station-sessions
    public function index(Request $request)
    {
        $query = ReviewStationSession::query();

        // دعم بسيط للـ pagination: /api/review-station-sessions?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/review-station-sessions/{id}
    public function show(ReviewStationSession $reviewStationSession)
    {
        return response()->json($reviewStationSession);
    }

    // POST /api/review-station-sessions
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'unit_id' => 'sometimes',
            'status' => 'sometimes',
            'points_earned' => 'sometimes',
            'started_at' => 'sometimes',
            'completed_at' => 'sometimes',
        ]);

        $reviewStationSession = ReviewStationSession::create($validated);

        return response()->json($reviewStationSession, 201);
    }

    // PUT/PATCH /api/review-station-sessions/{id}
    public function update(Request $request, ReviewStationSession $reviewStationSession)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'unit_id' => 'sometimes',
            'status' => 'sometimes',
            'points_earned' => 'sometimes',
            'started_at' => 'sometimes',
            'completed_at' => 'sometimes',
        ]);

        $reviewStationSession->update($validated);

        return response()->json($reviewStationSession);
    }

    // DELETE /api/review-station-sessions/{id}
    public function destroy(ReviewStationSession $reviewStationSession)
    {
        $reviewStationSession->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
