<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentGiftLog;
use Illuminate\Http\Request;

class StudentGiftLogController extends Controller
{
    // GET /api/student-gifts-log
    public function index(Request $request)
    {
        $query = StudentGiftLog::query();

        // دعم بسيط للـ pagination: /api/student-gifts-log?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/student-gifts-log/{id}
    public function show(StudentGiftLog $studentGiftLog)
    {
        return response()->json($studentGiftLog);
    }

    // POST /api/student-gifts-log
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'unit_id' => 'sometimes',
            'reward_type' => 'sometimes',
            'points_amount' => 'sometimes',
            'store_item_id' => 'sometimes',
            'granted_at' => 'sometimes',
        ]);

        $studentGiftLog = StudentGiftLog::create($validated);

        return response()->json($studentGiftLog, 201);
    }

    // PUT/PATCH /api/student-gifts-log/{id}
    public function update(Request $request, StudentGiftLog $studentGiftLog)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'unit_id' => 'sometimes',
            'reward_type' => 'sometimes',
            'points_amount' => 'sometimes',
            'store_item_id' => 'sometimes',
            'granted_at' => 'sometimes',
        ]);

        $studentGiftLog->update($validated);

        return response()->json($studentGiftLog);
    }

    // DELETE /api/student-gifts-log/{id}
    public function destroy(StudentGiftLog $studentGiftLog)
    {
        $studentGiftLog->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
