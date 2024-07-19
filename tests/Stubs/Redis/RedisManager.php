<?php

namespace Tests\Stubs\Redis;

use Illuminate\Redis\RedisManager as IlluminateRedisManager;

class RedisManager extends IlluminateRedisManager
{
    public function ping(string|array $message = null): string|bool
    {
        if (empty($message)) {
            return true;
        }

        return $message;
    }
}
