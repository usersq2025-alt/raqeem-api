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
use Illuminate\Support\Facades\Gate;
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
    }
}
