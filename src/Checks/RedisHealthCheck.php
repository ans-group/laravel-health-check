<?php

namespace UKFast\HealthCheck\Checks;

use Illuminate\Support\Facades\Redis;
use UKFast\HealthCheck\HealthCheck;
use Exception;

class RedisHealthCheck extends HealthCheck
{
    const NAME = 'redis';

    protected $name = self::NAME;

    public function status()
    {
        try {
            Redis::ping();
        } catch (Exception $e) {
            return $this->problem('Failed to connect to redis', [
                'exception' => $this->exceptionContext($e),
            ]);
        }
        return $this->okay();
    }
}
