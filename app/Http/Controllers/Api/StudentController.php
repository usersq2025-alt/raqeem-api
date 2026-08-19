<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StoreItem;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // GET /api/students
    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $query = Student::where('parent_id', $request->user()->id);

        // دعم بسيط للـ pagination: /api/students?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/students/{id}
    public function show(Student $student)
    {
        $this->authorize('view', $student);

        return response()->json($student);
    }

    // POST /api/students
    public function store(Request $request)
    {
        $this->authorize('create', Student::class);

        $validated = $request->validate([
            'public_id' => 'sometimes',
            'full_name' => 'sometimes',
            'birth_date' => 'sometimes',
            'grade_id' => 'sometimes',
            'gender' => 'sometimes',
            'profession_id' => 'sometimes',
            'streak_current' => 'sometimes',
            'streak_longest' => 'sometimes',
            'last_activity_date' => 'sometimes',
            'status' => 'sometimes',
        ]);

        // parent_id يُفرض دائمًا من المستخدم المصادَق، لا يُقرأ من الطلب أبدًا
        // (يمنع إلحاق طفل جديد بحساب ولي أمر آخر عبر تمرير parent_id مزوَّر)
        $validated['parent_id'] = $request->user()->id;

        // B2: رصيد ابتدائي = سعر أرخص عنصر نشط بالمتجر حاليًا (0 إن لم يوجد أي عنصر نشط بعد).
        // points_balance محذوف عمدًا من حقول الطلب المقبولة: هو "cache" مصدر حقيقته
        // points_transactions (حسب تعليق الـ schema)، فلا يجوز أن يُضبط مباشرة من العميل
        // إطلاقًا — لا هنا ولا بمسار update — وإلا كان بالإمكان تزوير رصيد عبر PATCH بسيط
        // رغم كل عمل المعاملات الذرية بهذه المرحلة.
        $validated['points_balance'] = (int) (StoreItem::where('is_active', true)->min('price_points') ?? 0);

        $student = Student::create($validated);

        return response()->json($student, 201);
    }

    // PUT/PATCH /api/students/{id}
    public function update(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'public_id' => 'sometimes',
            'full_name' => 'sometimes',
            'birth_date' => 'sometimes',
            'grade_id' => 'sometimes',
            'gender' => 'sometimes',
            'profession_id' => 'sometimes',
            'streak_current' => 'sometimes',
            'streak_longest' => 'sometimes',
            'last_activity_date' => 'sometimes',
            'status' => 'sometimes',
        ]);

        // parent_id وpoints_balance غير قابلين للتعديل عبر هذا المسار أبدًا
        $student->update($validated);

        return response()->json($student);
    }

    // DELETE /api/students/{id}
    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        $student->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
