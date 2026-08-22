<?php

use App\Exceptions\Gameplay\GameplayException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // هذا مشروع API بحت (لا مسار 'login' حقيقي)، فتعطيل إعادة التوجيه الافتراضية
        // ضروري: بدونه، أي طلب غير مُصادَق لا يرسل Accept: application/json بدقة
        // (بعض العملاء الحقيقيين لا يضمنون ذلك) يتسبَّب بخطأ 500 حقيقي
        // ("Route [login] not defined") بدل 401 نظيفة — اكتُشفت هذه أثناء اختبار D6.
        $middleware->redirectGuestsTo(fn () => null);

        // SEC-06 — throttle:api-general (مُعرَّف بـ AppServiceProvider::configureRateLimiting)
        // يُضاف تلقائيًا لمجموعة middleware 'api' كاملة، فيشمل كل route حالي ومستقبلي
        // بـ routes/api.php بلا حاجة لإضافته يدويًا بكل مسار جديد. المسارات الحساسة
        // (verify-otp، admin/login، forgot-*) تبقى بحدودها الأضيق الخاصة فوق هذا الحد
        // العام — كلاهما يعملان معًا، والأضيق يرفض أولًا لأن حده أقل.
        $middleware->throttleApi('api-general');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(fn (GameplayException $e) => response()->json(
            array_merge(['message' => $e->getMessage()], $e->extra()),
            $e->statusCode()
        ));
    })->create();
