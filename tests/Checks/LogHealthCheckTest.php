<?php

namespace Tests\Checks;

use Illuminate\Foundation\Application;
use Psr\Log\LoggerInterface;
use Stringable;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use Exception;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class LogHealthCheckTest extends TestCase
{
    /**
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array
    {
        return [HealthCheckServiceProvider::class];
    }

    public function testShowsProblemIfCannotWriteToLogs(): void
    {
        $this->app->bind('log', function () {
            return new BadLogger;
        });

        $status = (new LogHealthCheck($this->app))->status();
        $this->assertTrue($status->isProblem());
    }

    public function testShowsOkayIfCanWriteToLogs(): void
    {
        $this->app->bind('log', function () {
            return new NullLogger;
        });

        $status = (new LogHealthCheck($this->app))->status();
        $this->assertTrue($status->isOkay());
    }
}

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

class NullLogger implements LoggerInterface
{

    public function emergency(Stringable | string $message, array $context = []): void
    {
    }

    public function alert(Stringable | string $message, array $context = []): void
    {
    }

    public function critical(Stringable | string $message, array $context = []): void
    {
    }

    public function error(Stringable | string $message, array $context = []): void
    {
    }

    public function warning(Stringable | string $message, array $context = []): void
    {
    }

    public function notice(Stringable | string $message, array $context = []): void
    {
        // TODO: Implement notice() method.
    }

    public function info(Stringable | string $message, array $context = []): void
    {
    }

    public function debug(Stringable | string $message, array $context = []): void
    {
    }

    public function log($level, Stringable | string $message, array $context = []): void
    {
    }
}
