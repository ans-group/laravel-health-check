<?php

namespace Tests\Facade;

use Tests\TestCase;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use UKFast\HealthCheck\Facade\HealthCheck;

class HealthCheckTest extends TestCase
{
    /**
     * @test
     */
    public function can_use_app_health_from_facade()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => [\UKFast\HealthCheck\Checks\EnvHealthCheck::class]]);
        $this->assertEquals(1, HealthCheck::all());
    }
}
