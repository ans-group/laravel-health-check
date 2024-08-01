<?php

namespace Tests;

use Artisan;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use URL;

class HealthCheckServiceProviderTest extends TestCase
{
    public function testConfiguresHealthcheckPackage(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        $this->assertNotNull(config('healthcheck'));
    }

    public function testRegistersHealthCheckRoute(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => []]);

        $response = $this->get('/health');
        $this->assertSame('{"status":"OK"}', $response->getContent());
    }

    public function testRegistersPingRoute(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => []]);

        $response = $this->get('/ping');
        $this->assertSame('pong', $response->getContent());
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

    #[DataProvider('routeProvider')]
    public function testUsesBasePathForHealthCheckRoutes(string $route): void
    {
        config(['healthcheck.base-path' => '/test/']);
        $this->app->register(HealthCheckServiceProvider::class);

        $routes = $this->app->make('router')->getRoutes();

        $this->assertNotNull($routes->match(Request::create('/test' . $route)));
        $this->expectException(NotFoundHttpException::class);
        $routes->match(Request::create($route));
    }

    public function testBasePathDefaultsToNothing(): void
    {
        config(['healthcheck.base-path' => '']);
        $this->app->register(HealthCheckServiceProvider::class);

        $routes = $this->app->make('router')->getRoutes();

        $this->assertNotNull($routes->match(Request::create('/ping')));
        $this->assertNotNull($routes->match(Request::create('/health')));
    }

    public function testRegisteredRouteHasAName(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);
        $routes = $this->app->make('router')->getRoutes();
        $this->assertEquals(config('healthcheck.route-name'), $routes->match(Request::create('/health'))->getName());
    }

    public function testHealthNameCanBeUsedForRouteGeneration(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        if (str_starts_with(phpversion(), '5.') || str_starts_with(phpversion(), '7.0')) {
            $this->markTestSkipped('URL::signedRoute does not exists');
        }

        $url = URL::signedRoute(config('healthcheck.route-name'));
        $this->assertNotNull($url);
    }

    public static function routeProvider(): array
    {
        return [
            'ping' => [
                'route' => '/ping',
            ],
            'health' => [
                'route' => '/health',
            ],
        ];
    }
}
