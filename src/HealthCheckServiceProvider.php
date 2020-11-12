<?php

namespace UKFast\HealthCheck;

use Illuminate\Support\ServiceProvider;
use UKFast\HealthCheck\Checks\CacheHealthCheck;
use UKFast\HealthCheck\Checks\CrossServiceHealthCheck;
use UKFast\HealthCheck\Checks\DatabaseHealthCheck;
use UKFast\HealthCheck\Checks\EnvHealthCheck;
use UKFast\HealthCheck\Checks\HttpHealthCheck;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use UKFast\HealthCheck\Checks\MigrationUpToDateHealthCheck;
use UKFast\HealthCheck\Checks\PackageSecurityHealthCheck;
use UKFast\HealthCheck\Checks\RedisHealthCheck;
use UKFast\HealthCheck\Checks\SchedulerHealthCheck;
use UKFast\HealthCheck\Checks\StorageHealthCheck;
use UKFast\HealthCheck\Commands\CacheSchedulerRunning;
use UKFast\HealthCheck\Controllers\PingController;
use UKFast\HealthCheck\Controllers\HealthCheckController;

class HealthCheckServiceProvider extends ServiceProvider
{
    const CHECK_BIND_PREFIX = 'hc_';

    public function boot()
    {
        $this->configure();
        $this->registerHealthCheckRoute();
        $this->registerPingRoute();
        $this->bindChecks();
        $this->appHealthSetup();

        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheSchedulerRunning::class,
            ]);
        }
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

    private function registerHealthCheckRoute()
    {
        $this->app->make('router')->get($this->withBasePath('/health'), [
            'middleware' => config('healthcheck.middleware'),
            'uses' => HealthCheckController::class,
        ]);
    }

    private function registerPingRoute()
    {
        $this->app->make('router')->get($this->withBasePath('/ping'), PingController::class);
    }

    private function bindChecks()
    {
        $checks = [
            CacheHealthCheck::NAME => CacheHealthCheck::class,
            CrossServiceHealthCheck::NAME => CrossServiceHealthCheck::class,
            DatabaseHealthCheck::NAME => DatabaseHealthCheck::class,
            EnvHealthCheck::NAME => EnvHealthCheck::class,
            HttpHealthCheck::NAME => HttpHealthCheck::class,
            LogHealthCheck::NAME => LogHealthCheck::class,
            MigrationUpToDateHealthCheck::NAME => MigrationUpToDateHealthCheck::class,
            PackageSecurityHealthCheck::NAME => PackageSecurityHealthCheck::class,
            RedisHealthCheck::NAME => RedisHealthCheck::class,
            SchedulerHealthCheck::NAME => SchedulerHealthCheck::class,
            StorageHealthCheck::NAME => StorageHealthCheck::class,
        ];

        /** @var HealthCheck $checkClass */
        foreach ($checks as $name => $checkClass)
        {
            $alias = self::CHECK_BIND_PREFIX . $name;
            $this->app->bind($alias, $checkClass);
        }
    }

    private function appHealthSetup()
    {
        $this->app->bind('app-health', function ($app) {
            $checks = collect();
            $checkNames = $app->config->get('healthcheck.checks');
            if (!is_array($checkNames)) {
                $checkNames = explode(',', $checkNames);
            }
            foreach ($checkNames as $checkName) {
                if (!class_exists($checkName)) {
                    //bind alias
                    $checkName = self::CHECK_BIND_PREFIX . $checkName;
                }

                $checks->push($app->make($checkName));
            }
            return new AppHealth($checks);
        });
    }
}
