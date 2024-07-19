<?php

namespace Tests\Stubs\Redis\Connections;

use Illuminate\Redis\Connections\PhpRedisConnection as IlluminatePhpRedisConnection;

class PhpRedisConnection extends IlluminatePhpRedisConnection
{
    public function ping(string|array $message = null): string|bool
    {
        if (empty($message)) {
            return true;
        }

        return $message;
    }
}