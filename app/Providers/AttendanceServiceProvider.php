<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\AttendanceServiceInterface;
use App\Services\AttendanceService; // Import the AttendanceService class

class AttendanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register() {
        $this->app->bind(
            AttendanceServiceInterface::class,
            AttendanceService::class
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
