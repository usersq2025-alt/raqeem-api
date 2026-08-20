<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExcelImport;
use Illuminate\Http\Request;

class ExcelImportController extends Controller
{
    // GET /api/admin/excel-imports
    // القراءة فقط: الإنشاء والمعالجة يمرّان عبر مسارات دلالية منفصلة (D6، مرحلة لاحقة)
    public function index(Request $request)
    {
        $query = ExcelImport::query();

        // دعم بسيط للـ pagination: /api/admin/excel-imports?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/admin/excel-imports/{id}
    public function show(ExcelImport $excelImport)
    {
        return response()->json($excelImport);
    }
}
