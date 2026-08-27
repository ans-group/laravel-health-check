<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;
use UKFast\HealthCheck\HealthCheck;

class LogHealthCheck extends HealthCheck
{
    protected string $name = 'log';

    protected LoggerInterface $logger;

    /**
     * @throws BindingResolutionException
     */
    public function __construct(Container $container)
    {
        $this->logger = $container->make('log');
    }

    public function check(): void
    {
        try {
            $this->logger->info('Checking if logs are writable');
        } catch (Exception $exception) {
            $this->fail('Could not write to log file', [
                'exception' => $this->exceptionContext($exception),
            ]);
        }
    }
}
