<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExcelImportRow;
use Illuminate\Http\Request;

class ExcelImportRowController extends Controller
{
    // GET /api/excel-import-rows
    public function index(Request $request)
    {
        $query = ExcelImportRow::query();

        // دعم بسيط للـ pagination: /api/excel-import-rows?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/excel-import-rows/{id}
    public function show(ExcelImportRow $excelImportRow)
    {
        return response()->json($excelImportRow);
    }

    // POST /api/excel-import-rows
    public function store(Request $request)
    {
        $validated = $request->validate([
            'import_id' => 'sometimes',
            'row_number' => 'sometimes',
            'raw_data' => 'sometimes',
            'mapped_data' => 'sometimes',
            'status' => 'sometimes',
            'error_message' => 'sometimes',
            'created_question_id' => 'sometimes',
        ]);

        $excelImportRow = ExcelImportRow::create($validated);

        return response()->json($excelImportRow, 201);
    }

    // PUT/PATCH /api/excel-import-rows/{id}
    public function update(Request $request, ExcelImportRow $excelImportRow)
    {
        $validated = $request->validate([
            'import_id' => 'sometimes',
            'row_number' => 'sometimes',
            'raw_data' => 'sometimes',
            'mapped_data' => 'sometimes',
            'status' => 'sometimes',
            'error_message' => 'sometimes',
            'created_question_id' => 'sometimes',
        ]);

        $excelImportRow->update($validated);

        return response()->json($excelImportRow);
    }

    // DELETE /api/excel-import-rows/{id}
    public function destroy(ExcelImportRow $excelImportRow)
    {
        $excelImportRow->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
