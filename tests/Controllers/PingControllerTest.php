<?php

namespace Tests\Controllers;

use Tests\TestCase;
use UKFast\HealthCheck\Controllers\PingController;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class PingControllerTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    public function testReturnsPong()
    {
        $this->assertSame('pong', (new PingController)->__invoke());
    }

    public function testOverridesDefaultPath()
    {
        config([
            'healthcheck.route-paths.ping' => '/pingz',
        ]);

        // Manually re-boot the service provider to override the path
        $this->app->getProvider(HealthCheckServiceProvider::class)->boot();

        $response = $this->get('/pingz');

        $this->assertSame('pong', $response->getContent());
    }
}
