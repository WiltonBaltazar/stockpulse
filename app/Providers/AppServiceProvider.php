<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensurePdfRuntimeDirectories();

        User::observe(UserObserver::class);
        Order::observe(OrderObserver::class);
    }

    private function ensurePdfRuntimeDirectories(): void
    {
        $directories = [
            storage_path('fonts'),
            storage_path('app/dompdf/temp'),
            storage_path('app/dompdf/logs'),
        ];

        foreach ($directories as $path) {
            File::ensureDirectoryExists($path, 0775, true);
        }
    }
}
