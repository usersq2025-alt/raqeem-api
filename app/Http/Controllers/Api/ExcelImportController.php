<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExcelImport;
use Illuminate\Http\Request;

class ExcelImportController extends Controller
{
    // GET /api/excel-imports
    public function index(Request $request)
    {
        $query = ExcelImport::query();

        // دعم بسيط للـ pagination: /api/excel-imports?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/excel-imports/{id}
    public function show(ExcelImport $excelImport)
    {
        return response()->json($excelImport);
    }

    // POST /api/excel-imports
    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_user_id' => 'sometimes',
            'file_name' => 'sometimes',
            'status' => 'sometimes',
            'total_rows' => 'sometimes',
            'success_rows' => 'sometimes',
            'error_rows' => 'sometimes',
        ]);

        $excelImport = ExcelImport::create($validated);

        return response()->json($excelImport, 201);
    }

    // PUT/PATCH /api/excel-imports/{id}
    public function update(Request $request, ExcelImport $excelImport)
    {
        $validated = $request->validate([
            'admin_user_id' => 'sometimes',
            'file_name' => 'sometimes',
            'status' => 'sometimes',
            'total_rows' => 'sometimes',
            'success_rows' => 'sometimes',
            'error_rows' => 'sometimes',
        ]);

        $excelImport->update($validated);

        return response()->json($excelImport);
    }

    // DELETE /api/excel-imports/{id}
    public function destroy(ExcelImport $excelImport)
    {
        $excelImport->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
