<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentPurchase;
use Illuminate\Http\Request;

class StudentPurchaseController extends Controller
{
    // GET /api/student-purchases
    public function index(Request $request)
    {
        $this->authorize('viewAny', StudentPurchase::class);

        $query = StudentPurchase::whereHas(
            'student',
            fn ($q) => $q->where('parent_id', $request->user()->id)
        );

        // دعم بسيط للـ pagination: /api/student-purchases?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/student-purchases/{id}
    public function show(StudentPurchase $studentPurchase)
    {
        $this->authorize('view', $studentPurchase);

        return response()->json($studentPurchase);
    }

    // POST /api/student-purchases
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'store_item_id' => 'sometimes',
            'price_paid' => 'sometimes',
            'purchased_at' => 'sometimes',
        ]);

        $this->authorize('create', [StudentPurchase::class, $validated['student_id'] ?? null]);

        $studentPurchase = StudentPurchase::create($validated);

        return response()->json($studentPurchase, 201);
    }

    // PUT/PATCH /api/student-purchases/{id}
    public function update(Request $request, StudentPurchase $studentPurchase)
    {
        $this->authorize('update', $studentPurchase);

        $validated = $request->validate([
            'student_id' => 'sometimes',
            'store_item_id' => 'sometimes',
            'price_paid' => 'sometimes',
            'purchased_at' => 'sometimes',
        ]);

        $studentPurchase->update($validated);

        return response()->json($studentPurchase);
    }

    // DELETE /api/student-purchases/{id}
    public function destroy(StudentPurchase $studentPurchase)
    {
        $this->authorize('delete', $studentPurchase);

        $studentPurchase->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
