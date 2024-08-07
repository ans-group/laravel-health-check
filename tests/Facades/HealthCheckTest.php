<?php

namespace Tests\Facade;

use Tests\TestCase;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use UKFast\HealthCheck\Facade\HealthCheck;

class HealthCheckTest extends TestCase
{
    public function testCanUseAppHealthFromFacade(): void
    {
        $this->app->register(HealthCheckServiceProvider::class);

        config(['healthcheck.checks' => [\UKFast\HealthCheck\Checks\EnvHealthCheck::class]]);
        $this->assertCount(1, HealthCheck::all());
    }
}
