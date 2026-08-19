<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

// TODO: هذا الـ Controller أصبح حصريًا تحت /api/admin/questions (يعرض payload/explanation كاملة).
// لاحقًا (خارج نطاق هذه المرحلة) يلزم إنشاء نسخة منفصلة لواجهة الطالب لا تُظهر
// الإجابة الصحيحة ضمن payload قبل انتهاء المحاولة — انظر SEC-02 بتقرير التدقيق الأصلي.
class QuestionController extends Controller
{
    // GET /api/questions
    public function index(Request $request)
    {
        $query = Question::query();

        // دعم بسيط للـ pagination: /api/questions?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/questions/{id}
    public function show(Question $question)
    {
        return response()->json($question);
    }

    // POST /api/questions
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'sometimes',
            'skill_id' => 'sometimes',
            'game_type_id' => 'sometimes',
            'question_text' => 'sometimes',
            'image_media_id' => 'sometimes',
            'difficulty' => 'sometimes',
            'payload' => 'sometimes',
            'explanation' => 'sometimes',
            'status' => 'sometimes',
            'source' => 'sometimes',
            'created_by' => 'sometimes',
        ]);

        $question = Question::create($validated);

        return response()->json($question, 201);
    }

    // PUT/PATCH /api/questions/{id}
    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'lesson_id' => 'sometimes',
            'skill_id' => 'sometimes',
            'game_type_id' => 'sometimes',
            'question_text' => 'sometimes',
            'image_media_id' => 'sometimes',
            'difficulty' => 'sometimes',
            'payload' => 'sometimes',
            'explanation' => 'sometimes',
            'status' => 'sometimes',
            'source' => 'sometimes',
            'created_by' => 'sometimes',
        ]);

        $question->update($validated);

        return response()->json($question);
    }

    // DELETE /api/questions/{id}
    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
