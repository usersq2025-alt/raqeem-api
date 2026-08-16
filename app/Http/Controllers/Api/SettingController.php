<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // GET /api/settings
    public function index(Request $request)
    {
        $query = Setting::query();

        // دعم بسيط للـ pagination: /api/settings?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/settings/{id}
    public function show(Setting $setting)
    {
        return response()->json($setting);
    }

    // POST /api/settings
    public function store(Request $request)
    {
        $validated = $request->validate([
            'setting_key' => 'sometimes',
            'setting_value' => 'sometimes',
            'group_name' => 'sometimes',
        ]);

        $setting = Setting::create($validated);

        return response()->json($setting, 201);
    }

    // PUT/PATCH /api/settings/{id}
    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'setting_key' => 'sometimes',
            'setting_value' => 'sometimes',
            'group_name' => 'sometimes',
        ]);

        $setting->update($validated);

        return response()->json($setting);
    }

    // DELETE /api/settings/{id}
    public function destroy(Setting $setting)
    {
        $setting->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
