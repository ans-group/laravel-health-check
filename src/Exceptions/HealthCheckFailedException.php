<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Exceptions;

use RuntimeException;
use Throwable;
use UKFast\HealthCheck\HealthReport;

class HealthCheckFailedException extends RuntimeException
{
    /**
     * @param array<int|string, mixed> $context
     */
    public function __construct(
        protected string $checkName = 'application',
        string $message = '',
        protected array $context = [],
        protected HealthReport|null $report = null,
        int $code = 0,
        Throwable|null $previous = null,
    ) {
        parent::__construct(
            $message !== '' ? $message : "Health check [{$checkName}] failed",
            $code,
            $previous,
        );
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

    public function report(): HealthReport|null
    {
        return $this->report;
    }
}
