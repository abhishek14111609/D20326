<?php

namespace App\Providers;

use App\Services\AudioCallService;
use App\Services\AgoraService;
use Illuminate\Support\ServiceProvider;

class AudioCallServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AudioCallService::class, function ($app) {
            return new AudioCallService(
                $app->make(AgoraService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
