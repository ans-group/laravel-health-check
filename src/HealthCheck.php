<?php

declare(strict_types=1);

namespace UKFast\HealthCheck;

use Throwable;
use UKFast\HealthCheck\Exceptions\HealthCheckDegradedException;
use UKFast\HealthCheck\Exceptions\HealthCheckFailedException;
use UKFast\HealthCheck\Status;

abstract class HealthCheck
{
    protected string $name;

    /**
     * @var array<int|string, mixed>
     */
    protected array $passContext = [];

    abstract public function check(): void;

    /**
     * Run the check and return a Status snapshot. HTTP and Artisan use {@see check()}.
     */
    public function inspect(): Status
    {
        try {
            $this->check();

            return (new Status())
                ->okay()
                ->withName($this->name())
                ->withContext($this->passContext());
        } catch (HealthCheckDegradedException $exception) {
            return (new Status())
                ->degraded($exception->getMessage())
                ->withContext($exception->context())
                ->withName($exception->checkName());
        } catch (HealthCheckFailedException $exception) {
            return (new Status())
                ->problem($exception->getMessage())
                ->withContext($exception->context())
                ->withName($exception->checkName());
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function passContext(): array
    {
        return $this->passContext;
    }

    /**
     * @param array<int|string, mixed> $context
     */
    protected function pass(array $context = []): void
    {
        $this->passContext = $context;
    }

    /**
     * @param array<int|string, mixed> $context
     */
    protected function fail(string $message = '', array $context = []): never
    {
        throw new HealthCheckFailedException($this->name(), $message, $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    protected function degrade(string $message = '', array $context = []): never
    {
        throw new HealthCheckDegradedException($this->name(), $message, $context);
    }

    /**
     * Context safe to expose to an unauthenticated caller of the health
     * endpoint. Deliberately excludes file paths and stack traces — those
     * are reported to the application's logger instead, not shown in the
     * response.
     *
     * @return array<int|string, mixed>
     */
    protected function exceptionContext(Throwable $exception): array
    {
        try {
            report($exception);
        } catch (Throwable) {
            // Reporting itself failed (e.g. the logger is what's broken) —
            // don't let that mask the original check failure.
        }

        return [
            'error' => $exception->getMessage(),
            'class' => $exception::class,
        ];
    }
}
