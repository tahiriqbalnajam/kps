<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class StudentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
{
    $this->app->bind(
        \App\Services\Contracts\StudentServiceInterface::class,
        \App\Services\StudentService::class
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
