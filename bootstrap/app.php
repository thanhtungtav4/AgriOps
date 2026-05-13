<?php

use App\Http\Middleware\FarmScopeMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'farm.scope' => FarmScopeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                $errors = $e->errors();
                $firstField = array_key_first($errors);
                $firstMessage = $errors[$firstField][0] ?? 'Validation failed.';

                return new \Illuminate\Http\JsonResponse([
                    'error' => [
                        'code' => 'VALIDATION_REQUIRED',
                        'message' => $firstMessage,
                        'details' => [
                            'field' => $firstField,
                            'errors' => $errors,
                        ],
                        'trace_id' => $request->header('X-Trace-ID') ?? uniqid(),
                    ],
                    'errors' => $errors,
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->expectsJson()) {
                return new \Illuminate\Http\JsonResponse([
                    'error' => [
                        'code' => 'AUTH_RATE_LIMITED',
                        'message' => $e->getMessage() ?: 'Too Many Attempts.',
                        'trace_id' => $request->header('X-Trace-ID') ?? uniqid(),
                    ],
                ], 429);
            }
        });
    })->create();
