<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyActivityLog;
use Illuminate\Http\Request;

class DailyActivityLogController extends Controller
{
    // GET /api/daily-activity-log
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

    // POST /api/daily-activity-log
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'activity_date' => 'sometimes',
        ]);

        $this->authorize('create', [DailyActivityLog::class, $validated['student_id'] ?? null]);

        $dailyActivityLog = DailyActivityLog::create($validated);

        return response()->json($dailyActivityLog, 201);
    }

    // PUT/PATCH /api/daily-activity-log/{id}
    public function update(Request $request, DailyActivityLog $dailyActivityLog)
    {
        $this->authorize('update', $dailyActivityLog);

        $validated = $request->validate([
            'student_id' => 'sometimes',
            'activity_date' => 'sometimes',
        ]);

        $dailyActivityLog->update($validated);

        return response()->json($dailyActivityLog);
    }

    // DELETE /api/daily-activity-log/{id}
    public function destroy(DailyActivityLog $dailyActivityLog)
    {
        $this->authorize('delete', $dailyActivityLog);

        $dailyActivityLog->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
