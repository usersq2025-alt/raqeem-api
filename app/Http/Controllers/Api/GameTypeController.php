<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use Illuminate\Http\Request;

class GameTypeController extends Controller
{
    // GET /api/game-types
    public function index(Request $request)
    {
        $query = GameType::query();

        // دعم بسيط للـ pagination: /api/game-types?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/game-types/{id}
    public function show(GameType $gameType)
    {
        return response()->json($gameType);
    }

    // POST /api/game-types
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'sometimes',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $gameType = GameType::create($validated);

        return response()->json($gameType, 201);
    }

    // PUT/PATCH /api/game-types/{id}
    public function update(Request $request, GameType $gameType)
    {
        $validated = $request->validate([
            'code' => 'sometimes',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $gameType->update($validated);

        return response()->json($gameType);
    }

    // DELETE /api/game-types/{id}
    public function destroy(GameType $gameType)
    {
        $gameType->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
