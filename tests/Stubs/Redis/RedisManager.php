<?php

namespace Tests\Stubs\Redis;

use Illuminate\Redis\RedisManager as IlluminateRedisManager;

class RedisManager extends IlluminateRedisManager
{
    /**
     * @param string|array<int, string>|null $message
     */
    public function ping(string|array|null $message = null): string|bool
    {
        if (empty($message)) {
            return true;
        }

        return $message;
    }
}
