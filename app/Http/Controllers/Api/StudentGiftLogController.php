<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentGiftLog;
use Illuminate\Http\Request;

class StudentGiftLogController extends Controller
{
    // GET /api/student-gifts-log
    // القراءة فقط: كل المنح تمر حصرًا عبر UnitGiftService (يُستدعى من LessonAttemptService::complete)
    public function index(Request $request)
    {
        $this->authorize('viewAny', StudentGiftLog::class);

        $query = StudentGiftLog::whereHas(
            'student',
            fn ($q) => $q->where('parent_id', $request->user()->id)
        );

        // دعم بسيط للـ pagination: /api/student-gifts-log?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/student-gifts-log/{id}
    public function show(StudentGiftLog $studentGiftLog)
    {
        $this->authorize('view', $studentGiftLog);

        return response()->json($studentGiftLog);
    }
}
