<?php

namespace Tests\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;
use Tests\Stubs\Checks\AlwaysDegradedCheck;
use Tests\Stubs\Checks\AlwaysDownCheck;
use Tests\Stubs\Checks\AlwaysUpCheck;
use Tests\TestCase;
use UKFast\HealthCheck\Controllers\HealthCheckController;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class HealthCheckControllerTest extends TestCase
{
    /**
     * @inheritDoc
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array
    {
        return [HealthCheckServiceProvider::class];
    }

    public function testReturnsOverallStatusOfOkayWhenEverythingIsUp(): void
    {
        $this->setChecks([AlwaysUpCheck::class]);
        $response = (new HealthCheckController())->__invoke($this->app);

        $this->assertSame([
            'status' => 'OK',
            'always-up' => ['status' => 'OK'],
        ], json_decode($response->getContent(), true));
    }

    public function testOverridesDefaultPingPath(): void
    {
        app('router')->setRoutes(app(RouteCollection::class));

        $response = $this->get('/ping');
        $response->assertStatus(404);

        $response = $this->get('/pingz');
        $response->assertStatus(404);

        config([
            'healthcheck.route-paths.ping' => '/pingz',
        ]);

        // Manually re-boot the service provider to override the path
        $this->app->getProvider(HealthCheckServiceProvider::class)->boot();

        $this->setChecks([AlwaysUpCheck::class]);

        $response = $this->get('/pingz');
        $this->assertSame('pong', $response->getContent());

        $response = $this->get('/ping');
        $response->assertStatus(404);
    }

    public function testOverridesDefaultHealthPath(): void
    {
        app('router')->setRoutes(app(RouteCollection::class));

        $response = $this->get('/health');
        $response->assertStatus(404);

        $response = $this->get('/healthz');
        $response->assertStatus(404);

        config([
            'healthcheck.route-paths.health' => '/healthz',
        ]);

        // Manually re-boot the service provider to override the path
        $this->app->getProvider(HealthCheckServiceProvider::class)->boot();

        $this->setChecks([AlwaysUpCheck::class]);

        $response = $this->get('/healthz');
        $this->assertSame([
            'status' => 'OK',
            'always-up' => ['status' => 'OK'],
        ], json_decode($response->getContent(), true));

        $response = $this->get('/health');
        $response->assertStatus(404);
    }

    public function testDefaultsThePingPathIfConfigIsNotSet(): void
    {
        app('router')->setRoutes(app(RouteCollection::class));

        $response = $this->get('/ping');
        $response->assertStatus(404);

        config([
            'healthcheck.route-paths' => null,
        ]);

        // Manually re-boot the service provider to override the path
        $this->app->getProvider(HealthCheckServiceProvider::class)->boot();

        $this->setChecks([AlwaysUpCheck::class]);

        $response = $this->get('/ping');
        $this->assertSame('pong', $response->getContent());
    }

    public function testDefaultsTheHealthPathIfConfigIsNotSet(): void
    {
        config([
            'healthcheck.route-paths' => null,
        ]);

        app('router')->setRoutes(app(RouteCollection::class));

        $response = $this->get('/health');


        $this->app->getProvider(HealthCheckServiceProvider::class)->boot();

        $this->setChecks([AlwaysUpCheck::class]);

        $response = $this->get('/health');

        $this->assertSame([
            'status' => 'OK',
            'always-up' => ['status' => 'OK'],
        ], json_decode($response->getContent(), true));
    }

    public function testReturnsDegradedStatusWithResponseCode200WhenServiceIsDegraded(): void
    {
        $this->setChecks([AlwaysUpCheck::class, AlwaysDegradedCheck::class]);
        $response = (new HealthCheckController())->__invoke($this->app);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            'status' => 'DEGRADED',
            'always-up' => ['status' => 'OK'],
            'always-degraded' => [
                'status' => 'DEGRADED',
                'message' => 'Something went wrong',
                'context' => ['debug' => 'info'],
            ],
        ], json_decode($response->getContent(), true));
    }

    public function testReturnsStatusOfProblemWhenAProblemOccurs(): void
    {
        $this->setChecks([AlwaysUpCheck::class, AlwaysDownCheck::class]);
        $response = (new HealthCheckController())->__invoke($this->app);

        $this->assertSame([
            'status' => 'PROBLEM',
            'always-up' => ['status' => 'OK'],
            'always-down' => [
                'status' => 'PROBLEM',
                'message' => 'Something went wrong',
                'context' => ['debug' => 'info'],
            ],
        ], json_decode($response->getContent(), true));
    }

    public function testReturnsStatusOfProblemWhenBothDegradedAndProblemStatusesOccur(): void
    {
        $this->setChecks([AlwaysUpCheck::class, AlwaysDegradedCheck::class, AlwaysDownCheck::class]);
        $response = (new HealthCheckController())->__invoke($this->app);

        $this->assertSame([
            'status' => 'PROBLEM',
            'always-up' => ['status' => 'OK'],
            'always-degraded' => [
                'status' => 'DEGRADED',
                'message' => 'Something went wrong',
                'context' => ['debug' => 'info'],
            ],
            'always-down' => [
                'status' => 'PROBLEM',
                'message' => 'Something went wrong',
                'context' => ['debug' => 'info'],
            ],
        ], json_decode($response->getContent(), true));

        $this->setChecks([AlwaysUpCheck::class, AlwaysDownCheck::class, AlwaysDegradedCheck::class,]);
        $response = (new HealthCheckController())->__invoke($this->app);

        $this->assertSame([
            'status' => 'PROBLEM',
            'always-up' => ['status' => 'OK'],
            'always-down' => [
                'status' => 'PROBLEM',
                'message' => 'Something went wrong',
                'context' => ['debug' => 'info'],
            ],
            'always-degraded' => [
                'status' => 'DEGRADED',
                'message' => 'Something went wrong',
                'context' => ['debug' => 'info'],
            ],
        ], json_decode($response->getContent(), true));
    }

    protected function setChecks($checks): void
    {
        config(['healthcheck.checks' => $checks]);
    }
}
