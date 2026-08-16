<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreItem;
use Illuminate\Http\Request;

class StoreItemController extends Controller
{
    // GET /api/store-items
    public function index(Request $request)
    {
        $query = StoreItem::query();

        // دعم بسيط للـ pagination: /api/store-items?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/store-items/{id}
    public function show(StoreItem $storeItem)
    {
        return response()->json($storeItem);
    }

    // POST /api/store-items
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'sometimes',
            'name' => 'sometimes',
            'description' => 'sometimes',
            'image_media_id' => 'sometimes',
            'price_points' => 'sometimes',
            'unlock_type' => 'sometimes',
            'unlock_unit_id' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $storeItem = StoreItem::create($validated);

        return response()->json($storeItem, 201);
    }

    // PUT/PATCH /api/store-items/{id}
    public function update(Request $request, StoreItem $storeItem)
    {
        $validated = $request->validate([
            'category' => 'sometimes',
            'name' => 'sometimes',
            'description' => 'sometimes',
            'image_media_id' => 'sometimes',
            'price_points' => 'sometimes',
            'unlock_type' => 'sometimes',
            'unlock_unit_id' => 'sometimes',
            'is_active' => 'sometimes',
        ]);

        $storeItem->update($validated);

        return response()->json($storeItem);
    }

    // DELETE /api/store-items/{id}
    public function destroy(StoreItem $storeItem)
    {
        $storeItem->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
