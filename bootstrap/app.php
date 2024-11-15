<?php

use App\Http\Middleware\CheckRole;
use App\Services\ApiResponseService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            switch (true) {
                case $e instanceof ValidationException:
                    return ApiResponseService::error($e->getMessage(), 422, $e->errors());
                case $e instanceof AuthenticationException:
                    return ApiResponseService::error($e->getMessage(), 401);
                case $e instanceof AuthorizationException:
                    return ApiResponseService::error($e->getMessage(), 403);
            }
        });
    })
    ->create();
