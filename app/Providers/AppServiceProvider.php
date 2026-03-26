<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AgoraService;
use App\Services\AudioCallService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register AgoraService
        $this->app->singleton(AgoraService::class, function ($app) {
            return new AgoraService();
        });

        // Register AudioCallService and inject AgoraService
        $this->app->singleton(AudioCallService::class, function ($app) {
            return new AudioCallService(
                $app->make(AgoraService::class) // inject AgoraService here
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
