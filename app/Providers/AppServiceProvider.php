<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

use App\Services\TestService;
use App\Services\Contracts\ParentServiceInterface;
use App\Services\ParentService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TestService::class, function ($app) {
            return new TestService();
        });

        $this->app->bind(ParentServiceInterface::class, ParentService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->registerRoutes();

        if (config('app.env') !== 'production') {
            DB::listen(function (QueryExecuted $query) {
                Log::info('sql: ' . $query->sql . ' ' . implode(', ', $query->bindings));
            });
        }
    }

    /**
     * Register web + api routes with the App\Http\Controllers namespace.
     *
     * Replaces the Laravel 10 RouteServiceProvider. Routes use string
     * controller names (e.g. 'StudentController@index') that rely on this
     * namespace, which Laravel 11's withRouting() does not set by default.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::middleware('web')
            ->namespace('App\\Http\\Controllers')
            ->group(base_path('routes/web.php'));

        Route::prefix('api')
            ->middleware('api')
            ->namespace('App\\Http\\Controllers')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}