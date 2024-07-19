<?php

namespace Tests\Facade;

use Tests\TestCase;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use UKFast\HealthCheck\Facade\HealthCheck;

class HealthCheckTest extends TestCase
{
    public function testCanUseAppHealthFromFacade()
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => [\UKFast\HealthCheck\Checks\EnvHealthCheck::class]]);
        $this->assertSame(1, HealthCheck::all()->count());
    }
}
