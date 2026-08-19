<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StoreItem;
use App\Models\StudentPurchase;
use App\Services\StorePurchaseService;
use Illuminate\Http\Request;

class StudentPurchaseController extends Controller
{
    public function __construct(private StorePurchaseService $purchaseService)
    {
    }

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
    // C2: السعر يُقرأ دائمًا من store_items.price_points، لا يُقبل price_paid من العميل إطلاقًا
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer',
            'store_item_id' => 'required|integer',
        ]);

        $this->authorize('create', [StudentPurchase::class, $validated['student_id']]);

        $student = Student::findOrFail($validated['student_id']);
        $item = StoreItem::findOrFail($validated['store_item_id']);

        $purchase = $this->purchaseService->purchase($student, $item);

        return response()->json($purchase, 201);
    }

    // PUT/PATCH /api/student-purchases/{id}
    public function update(Request $request, StudentPurchase $studentPurchase)
    {
        $this->authorize('update', $studentPurchase);

        // price_paid وstudent_id وstore_item_id غير قابلة للتعديل بعد الإنشاء (تحافظ على سلامة السجل الذري)
        $validated = $request->validate([
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
