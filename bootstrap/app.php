<?php

use App\Exceptions\QuotaExceededException;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsurePlanAccess;
use App\Http\Middleware\NoCacheResponse;
use App\Http\Middleware\SetAcademicYear;
use App\Http\Middleware\SetSchoolContext;
use App\Http\Middleware\TrackPageVisit;
use App\Services\AcademicYearContext;
use App\Services\SchoolContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        $middleware->web(append: [
            SetSchoolContext::class,
            SetAcademicYear::class,
            EnsurePlanAccess::class,
            NoCacheResponse::class,
            TrackPageVisit::class,
            EnsurePasswordChanged::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('auth.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (QuotaExceededException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => ['quota' => $e->getMessage()]], 422);
            }

            return back()->with('error', $e->getMessage());
        });
    })
    ->withSchedule(function ($schedule): void {
        $schedule->command('school:check-expiry')->daily();
    })
    ->withSingletons([
        SchoolContext::class,
        AcademicYearContext::class,
    ])->create();
