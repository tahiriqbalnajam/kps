<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ParentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
{
    $this->app->bind(
        \App\Services\Contracts\ParentServiceInterface::class,
        \App\Services\ParentService::class
    );
}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
