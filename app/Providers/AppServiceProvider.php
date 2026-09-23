<?php

namespace App\Providers;

use App\Services\StaffWorkCounts;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The sidebar and activity tabs share one calculation per HTTP request.
        // A scoped binding remains safe under long-running workers such as Octane.
        $this->app->scoped(StaffWorkCounts::class, fn () => new StaffWorkCounts());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
