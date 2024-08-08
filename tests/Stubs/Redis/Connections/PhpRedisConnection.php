<?php

namespace Tests\Stubs\Redis\Connections;

use Illuminate\Redis\Connections\PhpRedisConnection as IlluminatePhpRedisConnection;

class PhpRedisConnection extends IlluminatePhpRedisConnection
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
