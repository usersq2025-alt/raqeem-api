<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PointsTransaction;
use Illuminate\Http\Request;

class PointsTransactionController extends Controller
{
    // GET /api/points-transactions
    public function index(Request $request)
    {
        $query = PointsTransaction::query();

        // دعم بسيط للـ pagination: /api/points-transactions?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/points-transactions/{id}
    public function show(PointsTransaction $pointsTransaction)
    {
        return response()->json($pointsTransaction);
    }

    // POST /api/points-transactions
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'type' => 'sometimes',
            'points_change' => 'sometimes',
            'reference_type' => 'sometimes',
            'reference_id' => 'sometimes',
        ]);

        $pointsTransaction = PointsTransaction::create($validated);

        return response()->json($pointsTransaction, 201);
    }

    // PUT/PATCH /api/points-transactions/{id}
    public function update(Request $request, PointsTransaction $pointsTransaction)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes',
            'type' => 'sometimes',
            'points_change' => 'sometimes',
            'reference_type' => 'sometimes',
            'reference_id' => 'sometimes',
        ]);

        $pointsTransaction->update($validated);

        return response()->json($pointsTransaction);
    }

    // DELETE /api/points-transactions/{id}
    public function destroy(PointsTransaction $pointsTransaction)
    {
        $pointsTransaction->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
