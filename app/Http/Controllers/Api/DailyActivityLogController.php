<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyActivityLog;
use Illuminate\Http\Request;

class DailyActivityLogController extends Controller
{
    // GET /api/daily-activity-log
    // القراءة فقط: كل الكتابة تمر حصرًا عبر StreakService (يُستدعى من LessonAttemptService::complete)
    public function index(Request $request)
    {
        $this->authorize('viewAny', DailyActivityLog::class);

        $query = DailyActivityLog::whereHas(
            'student',
            fn ($q) => $q->where('parent_id', $request->user()->id)
        );

        // دعم بسيط للـ pagination: /api/daily-activity-log?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/daily-activity-log/{id}
    public function show(DailyActivityLog $dailyActivityLog)
    {
        $this->authorize('view', $dailyActivityLog);

        return response()->json($dailyActivityLog);
    }
}
