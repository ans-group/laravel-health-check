<?php

namespace UKFast\HealthCheck\Checks;

use Illuminate\Redis\Connections\PhpRedisClusterConnection;
use Illuminate\Support\Facades\Redis;
use UKFast\HealthCheck\HealthCheck;
use Exception;
use UKFast\HealthCheck\Status;

class RedisHealthCheck extends HealthCheck
{
    protected string $name = 'redis';

    public function status(): Status
    {
        try {
            if ($this->isUsingPhpRedis()) {
                $this->handlePhpRedisPing();
            } else {
                // Think this is all we can do for predis
                Redis::ping();
            }
        } catch (Exception $e) {
            return $this->problem('Failed to connect to redis', [
                'exception' => $this->exceptionContext($e),
            ]);
        }
        return $this->okay();
    }

    protected function isUsingPhpRedis(): bool
    {
        return config('database.redis.client') == 'phpredis';
    }

    protected function handlePhpRedisPing(): void
    {
        $redis = Redis::connection();

        if (! $redis instanceof PhpRedisClusterConnection) {
            $redis->ping();

            return;
        }


        foreach ($redis->_masters() as $master) {
            $redis->ping($master);
        }
    }
}
