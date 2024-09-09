<?php

namespace Tests\Stubs\Log;

use Psr\Log\LoggerInterface;
use Stringable;

class NullLogger implements LoggerInterface
{
    /**
     * @inheritDoc
     */
    public function emergency(Stringable | string $message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function alert(Stringable | string $message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function critical(Stringable | string $message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function error(Stringable | string $message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function warning(Stringable | string $message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function notice(Stringable | string $message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function info(Stringable | string $message, array $context = []): void
    {
    }

    /**
     * @inheritDoc
     */
    public function debug(Stringable | string $message, array $context = []): void
    {
    }

    /**
     * @param mixed $level
     * @inheritDoc
     */
    public function log($level, Stringable | string $message, array $context = []): void
    {
    }
}
