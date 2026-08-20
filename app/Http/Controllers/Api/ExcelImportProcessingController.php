<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExcelImport;
use App\Models\ExcelImportRow;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;

class ExcelImportProcessingController extends Controller
{
    public function __construct(private ExcelImportService $service)
    {
    }

    // POST /api/admin/excel-imports/upload (multipart/form-data)
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $import = $this->service->import($validated['file'], $request->user());

        return response()->json([
            'import' => $import,
            'rows' => ExcelImportRow::where('import_id', $import->id)->get(),
        ], 201);
    }

    // PATCH /api/admin/excel-import-rows/{row}/mapping
    public function updateMapping(Request $request, ExcelImportRow $row)
    {
        $validated = $request->validate([
            'mapped_data' => 'required|array',
        ]);

        $row = $this->service->updateRowMapping($row, $validated['mapped_data']);

        return response()->json($row);
    }

    // POST /api/admin/excel-imports/{import}/confirm
    public function confirm(ExcelImport $import)
    {
        $import = $this->service->confirm($import);

        return response()->json($import);
    }
}
