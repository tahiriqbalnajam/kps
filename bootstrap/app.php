<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
        \App\Providers\AuthServiceProvider::class,
        \App\Providers\EventServiceProvider::class,
        \App\Providers\StudentServiceProvider::class,
        \App\Providers\AttendanceServiceProvider::class,
        \App\Providers\ExamServiceProvider::class,
        \App\Providers\ParentServiceProvider::class,
    ])
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API group: prepend Sanctum's stateful middleware. Laravel 11's default
        // `api` group already includes `throttle:api` + SubstituteBindings.
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Use the app's custom framework-middleware overrides.
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \App\Http\Middleware\TrimStrings::class
        );
        $middleware->replace(
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \App\Http\Middleware\EncryptCookies::class
        );
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );

        // Multi-tenant DB switcher — runs for every request before routing so
        // the tenant database is selected before any DB query is made.
        $middleware->append(\App\Http\Middleware\SubdomainDatabaseSwitcher::class);

        // Route middleware aliases (re-register the Laravel 10 Kernel aliases).
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.apikey' => \App\Http\Middleware\CheckApiKey::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $e) {
            \Illuminate\Support\Facades\Log::error($e->getMessage().$e->getTraceAsString());
        });
    })->create();