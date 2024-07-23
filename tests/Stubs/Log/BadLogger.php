<?php

namespace Tests\Stubs\Log;

use Exception;
use Psr\Log\LoggerInterface;
use Stringable;

class BadLogger implements LoggerInterface
{
    /**
     * @throws Exception
     */
    public function emergency(Stringable | string $message, array $context = []): void
    {
        throw new Exception('Failed to log');
    }

    /**
     * @throws Exception
     */
    public function alert(Stringable | string $message, array $context = []): void
    {
        throw new Exception('Failed to log');
    }

    /**
     * @throws Exception
     */
    public function critical(Stringable | string $message, array $context = []): void
    {
        throw new Exception('Failed to log');
    }

    /**
     * @throws Exception
     */
    public function error(Stringable | string $message, array $context = []): void
    {
        throw new Exception('Failed to log');
    }

    /**
     * @throws Exception
     */
    public function warning(Stringable | string $message, array $context = []): void
    {
        throw new Exception('Failed to log');
    }

    /**
     * @throws Exception
     */
    public function notice(Stringable | string $message, array $context = []): void
    {
        throw new Exception('Failed to log');
    }

    /**
     * @throws Exception
     */
    public function info(Stringable | string $message, array $context = []): void
    {
        throw new Exception('Failed to log');
    }

    /**
     * @throws Exception
     */
    public function debug(Stringable | string $message, array $context = []): void
    {
        throw new Exception('Failed to log');
    }

    /**
     * @throws Exception
     */
    public function log($level, Stringable | string $message, array $context = []): void
    {
        throw new Exception('Failed to log');
    }
}
