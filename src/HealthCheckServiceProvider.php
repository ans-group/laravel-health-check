<?php

namespace UKFast\HealthCheck;

use Illuminate\Support\ServiceProvider;
use UKFast\HealthCheck\Controllers\PingController;
use UKFast\HealthCheck\Controllers\HealthCheckController;

class HealthCheckServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->configure();
        $this->app->make('router')->get('/health', [
            'middleware' => config('healthcheck.middleware'),
            'uses' => HealthCheckController::class
        ]);

        $this->app->make('router')->get('/ping', PingController::class);
    }

    protected function configure()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/healthcheck.php', 'healthcheck');
        $configPath =  $this->app->basePath() . '/config/healthcheck.php';
        $this->publishes([
            __DIR__.'/../config/healthcheck.php' => $configPath,
        ], 'config');

        if ($this->app instanceof \Laravel\Lumen\Application) {
            $this->app->configure('healthcheck');
        }
    }
}
