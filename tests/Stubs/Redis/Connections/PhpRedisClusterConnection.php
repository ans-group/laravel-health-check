<?php

namespace Tests\Stubs\Redis\Connections;

use Illuminate\Redis\Connections\PhpRedisClusterConnection as IlluminatePhpRedisClusterConnection;

class PhpRedisClusterConnection extends IlluminatePhpRedisClusterConnection
{
    public function ping(string|array $message = null): string|bool
    {
        if (empty($message)) {
            return true;
        }

        return $message;
    }

    /**
     * @return array<string, string|int>
     */
    public function _masters(): array
    {
        return [];
    }
}
