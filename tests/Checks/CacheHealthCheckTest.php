<?php

namespace Tests\Checks;

use Exception;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use UKFast\HealthCheck\Checks\CacheHealthCheck;

class CacheHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    /**
     * @test
     */
    public function shows_problem_if_cannot_write_to_cache()
    {
        config([
            'healthcheck.cache.stores' => [
                'array'
            ]
        ]);

        Cache::shouldReceive('store')->andReturn(new BadStore());

        $status = (new CacheHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_okay_if_can_write_to_cache()
    {
        config([
            'healthcheck.cache.stores' => [
                'array'
            ]
        ]);
        
        $status = (new CacheHealthCheck($this->app))->status();

        $this->assertTrue($status->isOkay());
    }
}

class BadStore
{
    public function __call($name, $arguments)
    {
        throw new Exception();
    }
}