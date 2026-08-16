<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    // GET /api/skills
    public function index(Request $request)
    {
        $query = Skill::query();

        // دعم بسيط للـ pagination: /api/skills?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/skills/{id}
    public function show(Skill $skill)
    {
        return response()->json($skill);
    }

    // POST /api/skills
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes',
            'description' => 'sometimes',
        ]);

        $skill = Skill::create($validated);

        return response()->json($skill, 201);
    }

    // PUT/PATCH /api/skills/{id}
    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name' => 'sometimes',
            'description' => 'sometimes',
        ]);

        $skill->update($validated);

        return response()->json($skill);
    }

    // DELETE /api/skills/{id}
    public function destroy(Skill $skill)
    {
        $skill->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
