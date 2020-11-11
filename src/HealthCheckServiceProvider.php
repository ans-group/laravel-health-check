<?php

namespace UKFast\HealthCheck;

use Illuminate\Support\ServiceProvider;
use UKFast\HealthCheck\Commands\CacheSchedulerRunning;
use UKFast\HealthCheck\Commands\StatusCommand;
use UKFast\HealthCheck\Controllers\PingController;
use UKFast\HealthCheck\Controllers\HealthCheckController;

class HealthCheckServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->configure();
        $this->app->make('router')->get($this->withBasePath('/health'), [
            'middleware' => config('healthcheck.middleware'),
            'uses' => HealthCheckController::class
        ]);

        $this->app->bind('app-health', function ($app) {
            $checks = collect();
            foreach ($app->config->get('healthcheck.checks') as $classPath) {
                $checks->push($app->make($classPath));
            }
            return new AppHealth($checks);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheSchedulerRunning::class,
                StatusCommand::class,
            ]);
        }

        $this->app->make('router')->get($this->withBasePath('/ping'), PingController::class);
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

    private function withBasePath($path)
    {
        $path = trim($path, '/');
        $basePath = trim(config('healthcheck.base-path'), '/');

        if ($basePath == '') {
            return "/$path";
        }

        return "/$basePath/$path";
    }
}
