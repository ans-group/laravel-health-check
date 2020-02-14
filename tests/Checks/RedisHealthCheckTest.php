<?php

namespace Tests\Checks;

use Illuminate\Support\Facades\Redis;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\RedisHealthCheck;
use Exception;

class RedisHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    /**
     * @test
     */
    public function shows_problem_if_cannot_connect_to_redis()
    {
        Redis::swap(new BadRedis);

        $status = (new RedisHealthCheck)->status();
        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_okay_if_can_connect_to_redis()
    {
        Redis::swap(new HealthyRedis);

        $status = (new RedisHealthCheck)->status();
        $this->assertTrue($status->isOkay());
    }
}

class BadRedis
{
    public function __call($name, $args)
    {
        throw new Exception('cant connect');
    }
}

class HealthyRedis
{
    public function __call($name, $args)
    {
    }
}
