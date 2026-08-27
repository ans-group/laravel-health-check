<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Checks;

use Illuminate\Redis\Connections\PhpRedisClusterConnection;
use Illuminate\Support\Facades\Redis;
use RedisException;
use UKFast\HealthCheck\HealthCheck;
use Exception;

class RedisHealthCheck extends HealthCheck
{
    protected string $name = 'redis';

    public function check(): void
    {
        try {
            $this->handlePing();
        } catch (Exception $exception) {
            $this->fail('Failed to connect to redis', [
                'exception' => $this->exceptionContext($exception),
            ]);
        }
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
