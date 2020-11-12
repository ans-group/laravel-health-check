<?php

namespace Tests;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UKFast\HealthCheck\AppHealth;
use UKFast\HealthCheck\Checks\CacheHealthCheck;
use UKFast\HealthCheck\Checks\EnvHealthCheck;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use UKFast\HealthCheck\Commands\UpdateSchedulerTimestamp;
use \Artisan;

class HealthCheckServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function configures_healthcheck_package()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->assertNotNull(config('healthcheck'));
    }

    /**
     * @test
     */
    public function registers_health_check_route()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => []]);

        $response = $this->get('/health');
        $this->assertSame('{"status":"OK"}', $response->getContent());
    }

    /**
     * @test
     */
    public function registers_ping_route()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => []]);

        $response = $this->get('/ping');
        $this->assertSame('pong', $response->getContent());
    }

    /**
     * @test
     */
    public function registers_scheduler_health_check_command()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->assertArrayHasKey('health-check:cache-scheduler-running', Artisan::all());
    }

    /**
     * @test
     */
    public function binds_app_health()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => [EnvHealthCheck::class, CacheHealthCheck::NAME]]);
        /** @var AppHealth $appHealth */
        $appHealth = $this->app->make('app-health');

        $this->assertInstanceOf(AppHealth::class, $appHealth);
        $this->assertCount(2, $appHealth->all());
    }

    /**
     * @test
     */
    public function uses_base_path_for_health_check_routes()
    {
        config(['healthcheck.base-path' => '/test/']);
        $this->app->register(HealthCheckServiceProvider::class);

        $routes = $this->app->make('router')->getRoutes();

        $this->assertNotNull($routes->match(Request::create('/test/ping')));
        $this->expectException(NotFoundHttpException::class);
        $this->assertNull($routes->match(Request::create('/ping')));

        $this->assertNotNull($routes->match(Request::create('/test/health')));
        $this->expectException(NotFoundHttpException::class);
        $this->assertNull($routes->match(Request::create('/health')));
    }

    /**
     * @test
     */
    public function base_path_defaults_to_nothing()
    {
        config(['healthcheck.base-path' => '']);
        $this->app->register(HealthCheckServiceProvider::class);

        $routes = $this->app->make('router')->getRoutes();

        $this->assertNotNull($routes->match(Request::create('/ping')));
        $this->assertNotNull($routes->match(Request::create('/health')));
    }
}
