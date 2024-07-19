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

    public function testShowsProblemIfCannotWriteToCache()
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

    public function testShowsProblemIfIncorrectReadFromCache()
    {
        config([
            'healthcheck.cache.stores' => [
                'local'
            ]
        ]);

        Cache::shouldReceive('store')->with('local')->once()->andReturnSelf()
            ->shouldReceive('put')->once()
            ->shouldReceive('pull')->once()->andReturn('incorrect-string');

        $status = (new CacheHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    public function showsOkayIfCanWriteToCache()
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