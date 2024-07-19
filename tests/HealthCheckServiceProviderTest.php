<?php

namespace Tests;

use Artisan;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use URL;

class HealthCheckServiceProviderTest extends TestCase
{
    public function testConfiguresHealthcheckPackage()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->assertNotNull(config('healthcheck'));
    }

    public function testRegistersHealthCheckRoute()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => []]);

        $response = $this->get('/health');
        $this->assertSame('{"status":"OK"}', $response->getContent());
    }

    public function testRegistersPingRoute()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => []]);

        $response = $this->get('/ping');
        $this->assertSame('pong', $response->getContent());
    }

    public function testRegistersSchedulerHealthCheckCommand()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->assertArrayHasKey('health-check:cache-scheduler-running', Artisan::all());
    }

    public function testRegistersHealthCheckMakeCommand()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->assertArrayHasKey('make:check', Artisan::all());
    }

    public function testBindsAppHealth()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => [\UKFast\HealthCheck\Checks\EnvHealthCheck::class]]);

        $this->assertInstanceOf(\UKFast\HealthCheck\AppHealth::class, $this->app->make('app-health'));
    }

    public function testUsesBasePathForHealthCheckRoutes()
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

    public function testBasePathDefaultsToNothing()
    {
        config(['healthcheck.base-path' => '']);
        $this->app->register(HealthCheckServiceProvider::class);

        $routes = $this->app->make('router')->getRoutes();

        $this->assertNotNull($routes->match(Request::create('/ping')));
        $this->assertNotNull($routes->match(Request::create('/health')));
    }

    public function testRegisteredRouteHasAName()
    {
        $this->app->register(HealthCheckServiceProvider::class);
        $routes = $this->app->make('router')->getRoutes();
        $this->assertEquals(config('healthcheck.route-name'), $routes->match(Request::create('/health'))->getName());
    }

    public function testHealthNameCanBeUsedForRouteGeneration()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        if (substr(phpversion(), 0, 2) === '5.' || substr(phpversion(), 0, 3) === '7.0') {
            $this->markTestSkipped('URL::signedRoute does not exists');
        }

        $url = URL::signedRoute(config('healthcheck.route-name'));
        $this->assertNotNull($url);
    }
}
