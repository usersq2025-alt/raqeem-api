<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameQuestion;
use Illuminate\Http\Request;

class GameQuestionController extends Controller
{
    // GET /api/game-questions
    public function index(Request $request)
    {
        $query = GameQuestion::query();

        // دعم بسيط للـ pagination: /api/game-questions?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/game-questions/{id}
    public function show(GameQuestion $gameQuestion)
    {
        return response()->json($gameQuestion);
    }

    // POST /api/game-questions
    public function store(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'sometimes',
            'question_id' => 'sometimes',
            'sort_order' => 'sometimes',
        ]);

        $gameQuestion = GameQuestion::create($validated);

        return response()->json($gameQuestion, 201);
    }

    // PUT/PATCH /api/game-questions/{id}
    public function update(Request $request, GameQuestion $gameQuestion)
    {
        $validated = $request->validate([
            'game_id' => 'sometimes',
            'question_id' => 'sometimes',
            'sort_order' => 'sometimes',
        ]);

        $gameQuestion->update($validated);

        return response()->json($gameQuestion);
    }

    // DELETE /api/game-questions/{id}
    public function destroy(GameQuestion $gameQuestion)
    {
        $gameQuestion->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
