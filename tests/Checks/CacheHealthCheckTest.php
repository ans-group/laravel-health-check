<?php

namespace Tests\Checks;

use Illuminate\Foundation\Application;
use Tests\Stubs\Cache\BadStore;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use UKFast\HealthCheck\Checks\CacheHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class CacheHealthCheckTest extends TestCase
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

    public function testShowsProblemIfCannotWriteToCache(): void
    {
        config([
            'healthcheck.cache.stores' => [
                'array'
            ]
        ]);

        Cache::shouldReceive('store')->andReturn(new BadStore());

        $status = (new CacheHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsProblemIfIncorrectReadFromCache(): void
    {
        config([
            'healthcheck.cache.stores' => [
                'local'
            ]
        ]);

        Cache::shouldReceive('store')->with('local')->once()->andReturnSelf()
            ->shouldReceive('put')->once()
            ->shouldReceive('pull')->once()->andReturn('incorrect-string');

        $status = (new CacheHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsOkayIfCanWriteToCache(): void
    {
        config([
            'healthcheck.cache.stores' => [
                'array'
            ]
        ]);

        $status = (new CacheHealthCheck())->status();

        $this->assertTrue($status->isOkay());
    }
}
