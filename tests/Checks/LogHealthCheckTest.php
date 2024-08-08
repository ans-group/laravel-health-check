<?php

namespace Tests\Checks;

use Illuminate\Foundation\Application;
use Tests\Stubs\Log\BadLogger;
use Tests\Stubs\Log\NullLogger;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class LogHealthCheckTest extends TestCase
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

    public function testShowsProblemIfCannotWriteToLogs(): void
    {
        $this->app->bind('log', fn(): BadLogger => new BadLogger());

        $status = (new LogHealthCheck($this->app))->status();
        $this->assertTrue($status->isProblem());
    }

    public function testShowsOkayIfCanWriteToLogs(): void
    {
        $this->app->bind('log', fn(): \Tests\Stubs\Log\NullLogger => new NullLogger());

        $status = (new LogHealthCheck($this->app))->status();
        $this->assertTrue($status->isOkay());
    }
}
