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
     * This cannot be changed for PSR 12 compliance as it is stubbing a dependency
     * @return array<string, string|int>
     */
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    public function _masters(): array
    {
        return [];
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}
