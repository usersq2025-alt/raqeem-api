<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReviewStationSession;
use Illuminate\Http\Request;

class ReviewStationSessionController extends Controller
{
    // GET /api/review-station-sessions
    // القراءة فقط: الجلسات تُنشأ تلقائيًا عند إتمام درس بأخطاء (B8)، والكتابة
    // على الإجابات تمر عبر مسار دلالي منفصل (انظر B8 بمرحلة لاحقة)
    public function index(Request $request)
    {
        $this->authorize('viewAny', ReviewStationSession::class);

        $query = ReviewStationSession::whereHas(
            'student',
            fn ($q) => $q->where('parent_id', $request->user()->id)
        );

        // دعم بسيط للـ pagination: /api/review-station-sessions?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/review-station-sessions/{id}
    public function show(ReviewStationSession $reviewStationSession)
    {
        $this->authorize('view', $reviewStationSession);

        return response()->json($reviewStationSession);
    }
}
