<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    // GET /api/badges
    // القراءة فقط: كتالوج الشارات يُنشأ تلقائيًا (StreakService::awardEligibleBadges)
    // من إعدادات config/streak_rules.php عند أول منح فعلي، لا عبر هذا المسار
    public function index(Request $request)
    {
        $query = Badge::query();

        // دعم بسيط للـ pagination: /api/badges?per_page=20
        $perPage = $request->integer('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // GET /api/badges/{id}
    public function show(Badge $badge)
    {
        return response()->json($badge);
    }
}
