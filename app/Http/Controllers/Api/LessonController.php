<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    // GET /api/lessons
    public function index(Request $request)
    {
        $query = Lesson::query();

        // دعم بسيط للـ pagination: /api/lessons?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/lessons/{id}
    public function show(Lesson $lesson)
    {
        return response()->json($lesson);
    }

    // POST /api/lessons
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'sometimes',
            'title' => 'sometimes',
            'body_content' => 'sometimes',
            'sort_order' => 'sometimes',
            'status' => 'sometimes',
        ]);

        $lesson = Lesson::create($validated);

        return response()->json($lesson, 201);
    }

    // PUT/PATCH /api/lessons/{id}
    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'unit_id' => 'sometimes',
            'title' => 'sometimes',
            'body_content' => 'sometimes',
            'sort_order' => 'sometimes',
            'status' => 'sometimes',
        ]);

        $lesson->update($validated);

        return response()->json($lesson);
    }

    // DELETE /api/lessons/{id}
    public function destroy(Lesson $lesson)
    {
        $lesson->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
