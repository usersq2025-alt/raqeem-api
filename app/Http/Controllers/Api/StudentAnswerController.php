<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;

class StudentAnswerController extends Controller
{
    // GET /api/student-answers
    // القراءة فقط: كل الكتابة تمر حصرًا عبر LessonAttemptController@answer
    // (POST /api/attempts/{attempt}/answer) — لا مسار مباشر لإنشاء/تعديل/حذف إجابة
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
}
