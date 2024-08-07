<?php

namespace UKFast\HealthCheck;

use Illuminate\Support\ServiceProvider;
use UKFast\HealthCheck\Commands\StatusCommand;
use UKFast\HealthCheck\Commands\CacheSchedulerRunning;
use UKFast\HealthCheck\Commands\HealthCheckMakeCommand;
use UKFast\HealthCheck\Controllers\HealthCheckController;
use UKFast\HealthCheck\Controllers\PingController;

class HealthCheckServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configure();

        $this->app->make('router')
            ->get($this->withBasePath(config('healthcheck.route-paths.health', '/health')), [
                'middleware' => config('healthcheck.middleware'),
                'uses' => HealthCheckController::class,
                'as' => config('healthcheck.route-name')
            ]);

        $this->app->bind('app-health', function ($app): AppHealth {
            $checks = collect();
            foreach ($app->config->get('healthcheck.checks') as $classPath) {
                $checks->push($app->make($classPath));
            }

            return new AppHealth($checks);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheSchedulerRunning::class,
                HealthCheckMakeCommand::class,
                StatusCommand::class,
            ]);
        }

        $this->app->make('router')
            ->get($this->withBasePath(config('healthcheck.route-paths.ping', '/ping')), PingController::class);
    }

    protected function configure(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/healthcheck.php', 'healthcheck');
        $configPath = $this->app->basePath() . '/config/healthcheck.php';

        $this->publishes([
            __DIR__ . '/../config/healthcheck.php' => $configPath,
        ], 'config');

        if (class_exists(\Laravel\Lumen\Application::class) && $this->app instanceof \Laravel\Lumen\Application) {
            $this->app->configure('healthcheck');
        }
    }

    private function withBasePath(string $path): string
    {
        $path = trim($path, '/');
        $basePath = trim((string) config('healthcheck.base-path'), '/');

        if ($basePath === '') {
            return "/$path";
        }

        return "/$basePath/$path";
    }
}
