<?php

namespace App\Providers;

use App\Models\DailyActivityLog;
use App\Models\ParentUser;
use App\Models\PointsTransaction;
use App\Models\ReviewStationQuestion;
use App\Models\ReviewStationSession;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\StudentBadge;
use App\Models\StudentGiftLog;
use App\Models\StudentLessonAttempt;
use App\Models\StudentPurchase;
use App\Policies\DailyActivityLogPolicy;
use App\Policies\ParentUserPolicy;
use App\Policies\PointsTransactionPolicy;
use App\Policies\ReviewStationQuestionPolicy;
use App\Policies\ReviewStationSessionPolicy;
use App\Policies\StudentAnswerPolicy;
use App\Policies\StudentBadgePolicy;
use App\Policies\StudentGiftLogPolicy;
use App\Policies\StudentLessonAttemptPolicy;
use App\Policies\StudentPolicy;
use App\Policies\StudentPurchasePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(ParentUser::class, ParentUserPolicy::class);
        Gate::policy(StudentPurchase::class, StudentPurchasePolicy::class);
        Gate::policy(PointsTransaction::class, PointsTransactionPolicy::class);
        Gate::policy(StudentAnswer::class, StudentAnswerPolicy::class);
        Gate::policy(StudentLessonAttempt::class, StudentLessonAttemptPolicy::class);
        Gate::policy(StudentBadge::class, StudentBadgePolicy::class);
        Gate::policy(DailyActivityLog::class, DailyActivityLogPolicy::class);
        Gate::policy(ReviewStationSession::class, ReviewStationSessionPolicy::class);
        Gate::policy(ReviewStationQuestion::class, ReviewStationQuestionPolicy::class);
        Gate::policy(StudentGiftLog::class, StudentGiftLogPolicy::class);

        $this->configureRateLimiting();
    }

    // SEC-06 — حماية عامة تلقائية على كل مسار API (مطبَّقة عبر withMiddleware()->throttleApi('api-general')
    // بـ bootstrap/app.php، لا route واحدًا واحدًا)، لتفادي تكرار نسيان إضافة throttle لمسار جديد مستقبلًا
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api-general', function (Request $request) {
            // guard مستقل لكل من ولي الأمر والإدارة (المرحلتان 1-2)، لذا لا يوجد "مستخدم افتراضي"
            // واحد نتحقق منه؛ نتحقق من الاثنين صراحة أيًا كان الطرف المُصادَق فعليًا بهذا الطلب
            $user = $request->user('parent') ?: $request->user('admin');

            // المستخدم المصادَق: الحد على معرّفه هو (لا الـ IP) حتى لا يتشارك مستخدمون مختلفون
            // خلف نفس الشبكة (مدرسة/شركة) نفس الحد بالخطأ. غير مصادَق: الحد على الـ IP.
            $limit = $user
                ? Limit::perMinute(60)->by('api-general:user:'.get_class($user).':'.$user->getKey())
                : Limit::perMinute(30)->by('api-general:ip:'.$request->ip());

            return $limit->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'عدد الطلبات تجاوز الحد المسموح، حاول لاحقًا',
                    'retry_after_seconds' => (int) ($headers['Retry-After'] ?? 60),
                ], 429, $headers);
            });
        });
    }
}
