<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        if (config('app.env') !== 'production') {
            DB::listen(function (QueryExecuted $query) {
                Log::info('sql: ' . $query->sql . ' ' . implode(', ', $query->bindings));
            });
        }
    }
}
