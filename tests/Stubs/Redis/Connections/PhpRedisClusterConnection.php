<?php

namespace Tests\Stubs\Redis\Connections;

use Illuminate\Redis\Connections\PhpRedisClusterConnection as IlluminatePhpRedisClusterConnection;

class PhpRedisClusterConnection extends IlluminatePhpRedisClusterConnection
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

    /**
     * This cannot be changed for PSR 12 or PHPMD compliance as it is stubbing a dependency
     * @return array<string, string|int>
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    public function _masters(): array
    {
        return [];
    }
    // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
}
