<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnitCompletionReward;
use Illuminate\Http\Request;

class UnitCompletionRewardController extends Controller
{
    // GET /api/unit-completion-rewards
    public function index(Request $request)
    {
        $query = UnitCompletionReward::query();

        // دعم بسيط للـ pagination: /api/unit-completion-rewards?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/unit-completion-rewards/{id}
    public function show(UnitCompletionReward $unitCompletionReward)
    {
        return response()->json($unitCompletionReward);
    }

    // POST /api/unit-completion-rewards
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'sometimes',
            'reward_type' => 'sometimes',
            'points_amount' => 'sometimes',
            'store_item_id' => 'sometimes',
        ]);

        $unitCompletionReward = UnitCompletionReward::create($validated);

        return response()->json($unitCompletionReward, 201);
    }

    // PUT/PATCH /api/unit-completion-rewards/{id}
    public function update(Request $request, UnitCompletionReward $unitCompletionReward)
    {
        $validated = $request->validate([
            'unit_id' => 'sometimes',
            'reward_type' => 'sometimes',
            'points_amount' => 'sometimes',
            'store_item_id' => 'sometimes',
        ]);

        $unitCompletionReward->update($validated);

        return response()->json($unitCompletionReward);
    }

    // DELETE /api/unit-completion-rewards/{id}
    public function destroy(UnitCompletionReward $unitCompletionReward)
    {
        $unitCompletionReward->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
