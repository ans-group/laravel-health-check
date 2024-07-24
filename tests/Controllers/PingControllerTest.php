<?php

namespace Tests\Controllers;

use Illuminate\Foundation\Application;
use Tests\TestCase;
use UKFast\HealthCheck\Controllers\PingController;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class PingControllerTest extends TestCase
{
    /**
     * @inheritDoc
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array
    {
        return [\UKFast\HealthCheck\HealthCheckServiceProvider::class];
    }

    public function testReturnsPong(): void
    {
        $this->assertSame('pong', (new PingController())->__invoke());
    }

    public function testOverridesDefaultPath(): void
    {
        config([
            'healthcheck.route-paths.ping' => '/pingz',
        ]);

        /**
         * @var HealthCheckServiceProvider $provider
         */
        $provider = $this->app->getProvider(HealthCheckServiceProvider::class);
        $provider->boot();

        $response = $this->get('/pingz');

        $this->assertSame('pong', $response->getContent());
    }
}
