<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentBadge;
use Illuminate\Http\Request;

class StudentBadgeController extends Controller
{
    // GET /api/student-badges
    // القراءة فقط: كل المنح تمر حصرًا عبر StreakService (يُستدعى من LessonAttemptService::complete)
    public function index(Request $request)
    {
        $this->authorize('viewAny', StudentBadge::class);

        $query = StudentBadge::whereHas(
            'student',
            fn ($q) => $q->where('parent_id', $request->user()->id)
        );

        // دعم بسيط للـ pagination: /api/student-badges?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/student-badges/{id}
    public function show(StudentBadge $studentBadge)
    {
        $this->authorize('view', $studentBadge);

        return response()->json($studentBadge);
    }
}
