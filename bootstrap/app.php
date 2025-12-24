<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Health check должен быть без middleware, иначе сессии первыми проверят базу
            Route::get('/healthz', [\App\Http\Controllers\HealthController::class, 'check']);

            Route::middleware('api')->group(function () {

                // Версия 1 API
                Route::prefix('api/v1')
                ->group(base_path('routes/v1/api.php'));

                // Версия 2 API
                Route::prefix('api/v2')
                    ->group(base_path('routes/v2/api.php'));
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->throttleApi();
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            $handler = app(\App\Exceptions\Handler::class);
            return $handler->render($request, $e);
        });
        
        $exceptions->dontReportDuplicates();
    })->create();

