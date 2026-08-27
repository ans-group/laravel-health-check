<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Exceptions;

use RuntimeException;

class HealthCheckDegradedException extends RuntimeException
{
    /**
     * @param array<int|string, mixed> $context
     */
    public function __construct(
        protected string $checkName,
        string $message = '',
        protected array $context = [],
    ) {
        parent::__construct($message !== '' ? $message : "Health check [{$checkName}] is degraded");
    }

    public function checkName(): string
    {
        return $this->checkName;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
