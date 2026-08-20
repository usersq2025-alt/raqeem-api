<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParentUserController;
use App\Http\Controllers\Api\OtpCodeController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\ProfessionController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\MediaFileController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\GameTypeController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GameQuestionController;
use App\Http\Controllers\Api\StudentLessonAttemptController;
use App\Http\Controllers\Api\StudentAnswerController;
use App\Http\Controllers\Api\ReviewStationSessionController;
use App\Http\Controllers\Api\ReviewStationQuestionController;
use App\Http\Controllers\Api\DailyActivityLogController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\StudentBadgeController;
use App\Http\Controllers\Api\MotivationalPhraseController;
use App\Http\Controllers\Api\PointsTransactionController;
use App\Http\Controllers\Api\StoreItemController;
use App\Http\Controllers\Api\StudentPurchaseController;
use App\Http\Controllers\Api\UnitCompletionRewardController;
use App\Http\Controllers\Api\StudentGiftLogController;
use App\Http\Controllers\Api\AdminRoleController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\ExcelImportController;
use App\Http\Controllers\Api\ExcelImportRowController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\LessonAttemptController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login']);

// مسارات ولي الأمر/الطالب — auth:parent (guard مستقل، provider مقيَّد بـ ParentUser فقط)
Route::middleware('auth:parent')->group(function () {
    Route::apiResource('parents', ParentUserController::class);
    Route::apiResource('otp-codes', OtpCodeController::class);
    Route::apiResource('grades', GradeController::class);
    Route::apiResource('professions', ProfessionController::class);
    Route::apiResource('students', StudentController::class);
    Route::apiResource('subjects', SubjectController::class);
    Route::apiResource('media-files', MediaFileController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('lessons', LessonController::class);
    Route::apiResource('skills', SkillController::class);
    Route::apiResource('games', GameController::class);
    Route::apiResource('game-questions', GameQuestionController::class);
    // قراءة فقط: كل الكتابة (بدء/إجابة/إتمام) تمر حصرًا عبر LessonAttemptController أدناه
    Route::apiResource('student-lesson-attempts', StudentLessonAttemptController::class)->only(['index', 'show']);
    Route::apiResource('student-answers', StudentAnswerController::class)->only(['index', 'show']);
    Route::apiResource('review-station-sessions', ReviewStationSessionController::class);
    Route::apiResource('review-station-questions', ReviewStationQuestionController::class);
    Route::apiResource('daily-activity-log', DailyActivityLogController::class);
    Route::apiResource('badges', BadgeController::class);
    Route::apiResource('student-badges', StudentBadgeController::class);
    Route::apiResource('motivational-phrases', MotivationalPhraseController::class);
    Route::apiResource('points-transactions', PointsTransactionController::class);
    Route::apiResource('store-items', StoreItemController::class);
    Route::apiResource('student-purchases', StudentPurchaseController::class);
    Route::apiResource('unit-completion-rewards', UnitCompletionRewardController::class);
    Route::apiResource('student-gifts-log', StudentGiftLogController::class);

    // B5 + B6.1-B6.4 + B7 — مسارات دلالية لتشغيل الدرس (بدل CRUD عام على student-lesson-attempts/student-answers)
    Route::post('/lessons/{lesson}/attempts/start', [LessonAttemptController::class, 'start']);
    Route::post('/attempts/{attempt}/answer', [LessonAttemptController::class, 'answer']);
    Route::post('/attempts/{attempt}/complete', [LessonAttemptController::class, 'complete']);
});

// مسارات الإدارة — guard مستقل تمامًا (admin)، توكن ولي الأمر لا يعمل هنا إطلاقًا
Route::prefix('admin')->group(function () {
    // SEC-05-admin: نفس منطق حماية OTP من brute-force، مطبَّق هنا على تسجيل دخول الإدارة
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:admin')->group(function () {
        Route::apiResource('admin-roles', AdminRoleController::class);
        Route::apiResource('admin-users', AdminUserController::class);
        Route::apiResource('excel-imports', ExcelImportController::class);
        Route::apiResource('excel-import-rows', ExcelImportRowController::class);
        Route::apiResource('settings', SettingController::class);
        Route::apiResource('game-types', GameTypeController::class);
        Route::apiResource('questions', QuestionController::class);
    });
});