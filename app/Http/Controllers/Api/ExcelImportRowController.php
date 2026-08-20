<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExcelImportRow;
use Illuminate\Http\Request;

class ExcelImportRowController extends Controller
{
    // GET /api/admin/excel-import-rows
    // القراءة فقط: الإنشاء والتحقق والنشر يمرّان عبر مسارات دلالية منفصلة (D6، مرحلة لاحقة)
    public function index(Request $request)
    {
        $query = ExcelImportRow::query();

        // دعم بسيط للـ pagination: /api/admin/excel-import-rows?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/admin/excel-import-rows/{id}
    public function show(ExcelImportRow $excelImportRow)
    {
        return response()->json($excelImportRow);
    }
}
