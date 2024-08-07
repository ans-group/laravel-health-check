<?php

namespace UKFast\HealthCheck\Checks;

use Illuminate\Redis\Connections\PhpRedisClusterConnection;
use Illuminate\Support\Facades\Redis;
use RedisException;
use UKFast\HealthCheck\HealthCheck;
use Exception;
use UKFast\HealthCheck\Status;

class RedisHealthCheck extends HealthCheck
{
    protected string $name = 'redis';

    public function status(): Status
    {
        try {
            $this->handlePing();
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

    /**
     * @throws RedisException
     */
    protected function handlePhpRedisPing(): void
    {
        $redis = Redis::connection();

        if (! $redis instanceof PhpRedisClusterConnection) {
            $redis->ping();

            return;
        }

        if (method_exists($redis, '_masters') === false) {
            throw new RedisException('Masters not found.');
        }

        foreach ($redis->_masters() as $master) {
            $redis->ping($master);
        }
    }

    protected function handlePing(): void
    {
        if ($this->isUsingPhpRedis()) {
            $this->handlePhpRedisPing();

            return;
        }

        // Think this is all we can do for predis
        Redis::ping();
    }
}
