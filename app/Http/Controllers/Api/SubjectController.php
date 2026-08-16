<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // GET /api/subjects
    public function index(Request $request)
    {
        $query = Subject::query();

        // دعم بسيط للـ pagination: /api/subjects?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/subjects/{id}
    public function show(Subject $subject)
    {
        return response()->json($subject);
    }

    // POST /api/subjects
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'icon_url' => 'sometimes',
            'sort_order' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $subject = Subject::create($validated);

        return response()->json($subject, 201);
    }

    // PUT/PATCH /api/subjects/{id}
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'icon_url' => 'sometimes',
            'sort_order' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $subject->update($validated);

        return response()->json($subject);
    }

    // DELETE /api/subjects/{id}
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
