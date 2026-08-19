<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
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
            'points_balance' => 'sometimes',
            'streak_current' => 'sometimes',
            'streak_longest' => 'sometimes',
            'last_activity_date' => 'sometimes',
            'status' => 'sometimes',
        ]);

        // parent_id يُفرض دائمًا من المستخدم المصادَق، لا يُقرأ من الطلب أبدًا
        // (يمنع إلحاق طفل جديد بحساب ولي أمر آخر عبر تمرير parent_id مزوَّر)
        $validated['parent_id'] = $request->user()->id;

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
            'points_balance' => 'sometimes',
            'streak_current' => 'sometimes',
            'streak_longest' => 'sometimes',
            'last_activity_date' => 'sometimes',
            'status' => 'sometimes',
        ]);

        // parent_id غير قابل للتعديل عبر هذا المسار أبدًا (يمنع "نقل" الطفل لحساب آخر)
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
