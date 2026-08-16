<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    // GET /api/games
    public function index(Request $request)
    {
        $query = Game::query();

        // دعم بسيط للـ pagination: /api/games?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/games/{id}
    public function show(Game $game)
    {
        return response()->json($game);
    }

    // POST /api/games
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'sometimes',
            'game_type_id' => 'sometimes',
            'title' => 'sometimes',
            'sort_order' => 'sometimes',
            'randomize_order' => 'sometimes',
            'config' => 'sometimes',
            'status' => 'sometimes',
        ]);

        $game = Game::create($validated);

        return response()->json($game, 201);
    }

    // PUT/PATCH /api/games/{id}
    public function update(Request $request, Game $game)
    {
        $validated = $request->validate([
            'lesson_id' => 'sometimes',
            'game_type_id' => 'sometimes',
            'title' => 'sometimes',
            'sort_order' => 'sometimes',
            'randomize_order' => 'sometimes',
            'config' => 'sometimes',
            'status' => 'sometimes',
        ]);

        $game->update($validated);

        return response()->json($game);
    }

    // DELETE /api/games/{id}
    public function destroy(Game $game)
    {
        $game->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
