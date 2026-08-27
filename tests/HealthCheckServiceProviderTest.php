<?php

declare(strict_types=1);

namespace Tests;

use Artisan;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use Illuminate\Support\Facades\URL;

class HealthCheckServiceProviderTest extends TestCase
{
    public function testConfiguresHealthcheckPackage(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->assertNotNull(config('healthcheck'));
    }

    public function testRegistersUpRoute(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => []]);

        $this->getJson('/up')
            ->assertOk()
            ->assertJsonPath('status', 'up');
    }

    public function testRegistersACustomUpPath(): void
    {
        config([
            'healthcheck.path' => '/status',
            'healthcheck.checks' => [],
        ]);
        $this->app->register(HealthCheckServiceProvider::class);

        $this->getJson('/status')
            ->assertOk()
            ->assertJsonPath('status', 'up');
        $this->get('/up')->assertNotFound();
    }

    public function testDoesNotRegisterHealthRoute(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->get('/health')->assertNotFound();
    }

    public function testDoesNotRegisterAPingRoute(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->get('/ping')->assertNotFound();
    }

    public function testRegistersSchedulerHealthCheckCommand(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->assertArrayHasKey('health-check:cache-scheduler-running', Artisan::all());
    }

    public function testRegistersHealthCheckMakeCommand(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->assertArrayHasKey('make:check', Artisan::all());
    }

    public function testBindsAppHealth(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => [\UKFast\HealthCheck\Checks\EnvHealthCheck::class]]);

        $this->assertInstanceOf(\UKFast\HealthCheck\AppHealth::class, $this->app->make('app-health'));
    }

    public function testUsesBasePathForUpRoute(): void
    {
        config(['healthcheck.base-path' => '/test/']);
        $this->app->register(HealthCheckServiceProvider::class);

        $routes = $this->app->make('router')->getRoutes();

        $this->assertNotNull($routes->match(Request::create('/test/up')));
        $this->expectException(NotFoundHttpException::class);
        $routes->match(Request::create('/up'));
    }

    public function testBasePathDefaultsToNothing(): void
    {
        config(['healthcheck.base-path' => '']);
        $this->app->register(HealthCheckServiceProvider::class);

        $routes = $this->app->make('router')->getRoutes();

        $this->assertNotNull($routes->match(Request::create('/up')));
    }

    public function testRegisteredRouteHasAName(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);
        $routes = $this->app->make('router')->getRoutes();
        $this->assertEquals(config('healthcheck.route-name'), $routes->match(Request::create('/up'))->getName());
    }

    public function testHealthNameCanBeUsedForRouteGeneration(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $url = URL::signedRoute(config('healthcheck.route-name'));
        $this->assertNotNull($url);
    }
}
