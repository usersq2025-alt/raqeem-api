<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentLessonAttempt;
use Illuminate\Http\Request;

class StudentLessonAttemptController extends Controller
{
    // GET /api/student-lesson-attempts
    // القراءة فقط: كل الكتابة تمر حصرًا عبر LessonAttemptController
    // (/lessons/{lesson}/attempts/start ، /attempts/{attempt}/answer ، /attempts/{attempt}/complete)
    public function index(Request $request)
    {
        $this->authorize('viewAny', StudentLessonAttempt::class);

        $query = StudentLessonAttempt::whereHas(
            'student',
            fn ($q) => $q->where('parent_id', $request->user()->id)
        );

        // دعم بسيط للـ pagination: /api/student-lesson-attempts?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/student-lesson-attempts/{id}
    public function show(StudentLessonAttempt $studentLessonAttempt)
    {
        $this->authorize('view', $studentLessonAttempt);

        return response()->json($studentLessonAttempt);
    }
}
