<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MotivationalPhrase;
use Illuminate\Http\Request;

class MotivationalPhraseController extends Controller
{
    // GET /api/motivational-phrases
    public function index(Request $request)
    {
        $query = MotivationalPhrase::query();

        // دعم بسيط للـ pagination: /api/motivational-phrases?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/motivational-phrases/{id}
    public function show(MotivationalPhrase $motivationalPhrase)
    {
        return response()->json($motivationalPhrase);
    }

    // POST /api/motivational-phrases
    public function store(Request $request)
    {
        $validated = $request->validate([
            'text_ar' => 'sometimes',
            'text_en' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $motivationalPhrase = MotivationalPhrase::create($validated);

        return response()->json($motivationalPhrase, 201);
    }

    // PUT/PATCH /api/motivational-phrases/{id}
    public function update(Request $request, MotivationalPhrase $motivationalPhrase)
    {
        $validated = $request->validate([
            'text_ar' => 'sometimes',
            'text_en' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $motivationalPhrase->update($validated);

        return response()->json($motivationalPhrase);
    }

    // DELETE /api/motivational-phrases/{id}
    public function destroy(MotivationalPhrase $motivationalPhrase)
    {
        $motivationalPhrase->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
