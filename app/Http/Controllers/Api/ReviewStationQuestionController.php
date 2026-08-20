<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReviewStationQuestion;
use Illuminate\Http\Request;

class ReviewStationQuestionController extends Controller
{
    // GET /api/review-station-questions
    // القراءة فقط: تُنشأ تلقائيًا مع الجلسة (B8)، والتصحيح يمر عبر مسار دلالي منفصل
    public function index(Request $request)
    {
        $this->authorize('viewAny', ReviewStationQuestion::class);

        $query = ReviewStationQuestion::whereHas(
            'session.student',
            fn ($q) => $q->where('parent_id', $request->user()->id)
        );

        // دعم بسيط للـ pagination: /api/review-station-questions?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/review-station-questions/{id}
    public function show(ReviewStationQuestion $reviewStationQuestion)
    {
        $this->authorize('view', $reviewStationQuestion);

        return response()->json($reviewStationQuestion);
    }
}
