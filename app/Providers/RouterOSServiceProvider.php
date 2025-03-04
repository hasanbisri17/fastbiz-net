<?php

namespace App\Providers;

use App\Services\RouterOSService;
use Illuminate\Support\ServiceProvider;

class RouterOSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RouterOSService::class, function ($app) {
            $config = config('routeros.default');
            return new RouterOSService(
                $config['host'],
                $config['user'],
                $config['password'],
                $config['port']
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/routeros.php' => config_path('routeros.php'),
        ], 'routeros-config');
    }
}
