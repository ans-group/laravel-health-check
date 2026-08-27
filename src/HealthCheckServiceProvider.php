<?php

declare(strict_types=1);

namespace UKFast\HealthCheck;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use UKFast\HealthCheck\Commands\StatusCommand;
use UKFast\HealthCheck\Commands\CacheSchedulerRunning;
use UKFast\HealthCheck\Commands\HealthCheckMakeCommand;
use UKFast\HealthCheck\Controllers\UpController;
use UKFast\HealthCheck\Listeners\RunPackageHealthChecks;

class HealthCheckServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configure();

        if (class_exists(DiagnosingHealth::class)) {
            Event::listen(DiagnosingHealth::class, RunPackageHealthChecks::class);
        }

        $healthPath = $this->withBasePath((string) config('healthcheck.path', '/up'));

        if (class_exists(PreventRequestsDuringMaintenance::class)) {
            PreventRequestsDuringMaintenance::except($healthPath);
        }

        if ($this->routeAlreadyExists($healthPath)) {
            Log::warning(
                "laravel-health-check: a route already exists at [{$healthPath}]. If the framework's own "
                . "health route is also registered (bootstrap/app.php `health:`), omit that argument so this "
                . "package's route is the only one at this path."
            );
        }

        $this->app->make('router')
            ->get($healthPath, [
                'middleware' => config('healthcheck.middleware'),
                'uses' => UpController::class,
                'as' => config('healthcheck.route-name'),
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
    }

    protected function configure(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/healthcheck.php', 'healthcheck');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'healthcheck');

        $this->publishes([
            __DIR__ . '/../config/healthcheck.php' => $this->app->basePath() . '/config/healthcheck.php',
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/views' => $this->app->resourcePath('views/vendor/healthcheck'),
        ], 'healthcheck-views');

        if (function_exists('public_path')) {
            $this->publishes([
                __DIR__ . '/../stubs/ping' => public_path('ping'),
            ], 'healthcheck-ping');
        }
    }

    private function routeAlreadyExists(string $healthPath): bool
    {
        $uri = ltrim($healthPath, '/');

        foreach ($this->app->make('router')->getRoutes()->get('GET') as $route) {
            if ($route->uri() === $uri) {
                return true;
            }
        }

        return false;
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
