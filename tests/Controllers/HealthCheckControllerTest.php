<?php

namespace Tests\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;
use Tests\TestCase;
use UKFast\HealthCheck\Controllers\HealthCheckController;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class HealthCheckControllerTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    /**
     * @test
     */
    public function returns_overall_status_of_okay_when_everything_is_up()
    {
        $this->setChecks([AlwaysUpCheck::class]);
        $response = (new HealthCheckController)->__invoke($this->app);

        $this->assertSame([
            'status' => 'OK',
            'always-up' => ['status' => 'OK'],
        ], json_decode($response->getContent(), true));
    }

    /**
     * @test
     */
    public function overrides_default_ping_path()
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

    /**
     * @test
     */
    public function overrides_default_health_path()
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


        $this->assertSame([
            'status' => 'OK',
            'always-up' => ['status' => 'OK'],
        ], json_decode($response->getContent(), true));
    }

    /**
     * @test
     */
    public function returns_degraded_status_with_response_code_200_when_service_is_degraded()
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

    /**
     * @test
     */
    public function returns_status_of_problem_when_a_problem_occurs()
    {
        $this->setChecks([AlwaysUpCheck::class, AlwaysDownCheck::class]);
        $response = (new HealthCheckController)->__invoke($this->app);

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

    /**
     * @test
     */
    public function returns_status_of_problem_when_both_degraded_and_problem_statuses_occur()
    {
        $this->setChecks([AlwaysUpCheck::class, AlwaysDegradedCheck::class, AlwaysDownCheck::class]);
        $response = (new HealthCheckController)->__invoke($this->app);

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
    }

    protected function setChecks($checks)
    {
        config(['healthcheck.checks' => $checks]);
    }
}

class AlwaysUpCheck extends HealthCheck
{
    protected $name = 'always-up';

    public function status()
    {
        return $this->okay();
    }
}

class AlwaysDegradedCheck extends HealthCheck
{
    protected $name = 'always-degraded';

    public function status()
    {
        return $this->degraded('Something went wrong', [
            'debug' => 'info',
        ]);
    }
}

class AlwaysDownCheck extends HealthCheck
{
    protected $name = 'always-down';

    public function status()
    {
        return $this->problem('Something went wrong', [
            'debug' => 'info',
        ]);
    }
}
