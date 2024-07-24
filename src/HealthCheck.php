<?php

namespace UKFast\HealthCheck;

use Throwable;

abstract class HealthCheck
{
    protected string $name;

    abstract public function status(): Status;

    public function name(): string
    {
        return $this->name;
    }

    public function problem(string $message = '', array $context = []): Status
    {
        return (new Status())
            ->problem($message)
            ->withContext($context)
            ->withName($this->name());
    }

    public function degraded(string $message = '', array $context = []): Status
    {
        return (new Status())
            ->degraded($message)
            ->withContext($context)
            ->withName($this->name());
    }

    public function okay(array $context = []): Status
    {
        return (new Status())
            ->okay()
            ->withContext($context)
            ->withName($this->name());
    }

    /**
     * @return array<string, string|array|int>
     */
    protected function exceptionContext(Throwable $exception): array
    {
        return [
            'error' => $exception->getMessage(),
            'class' => $exception::class,
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
            'trace' => explode("\n", $exception->getTraceAsString()),
        ];
    }
}
