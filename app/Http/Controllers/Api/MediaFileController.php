<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use Illuminate\Http\Request;

class MediaFileController extends Controller
{
    // GET /api/media-files
    public function index(Request $request)
    {
        $query = MediaFile::query();

        // دعم بسيط للـ pagination: /api/media-files?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/media-files/{id}
    public function show(MediaFile $mediaFile)
    {
        return response()->json($mediaFile);
    }

    // POST /api/media-files
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'sometimes',
            'original_filename' => 'sometimes',
            'mime_type' => 'sometimes',
            'size_bytes' => 'sometimes',
            'uploaded_by' => 'sometimes',
        ]);

        $mediaFile = MediaFile::create($validated);

        return response()->json($mediaFile, 201);
    }

    // PUT/PATCH /api/media-files/{id}
    public function update(Request $request, MediaFile $mediaFile)
    {
        $validated = $request->validate([
            'url' => 'sometimes',
            'original_filename' => 'sometimes',
            'mime_type' => 'sometimes',
            'size_bytes' => 'sometimes',
            'uploaded_by' => 'sometimes',
        ]);

        $mediaFile->update($validated);

        return response()->json($mediaFile);
    }

    // DELETE /api/media-files/{id}
    public function destroy(MediaFile $mediaFile)
    {
        $mediaFile->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
