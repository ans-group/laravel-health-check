<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

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

    public function status(): Status
    {
        try {
            $this->logger->info('Checking if logs are writable');
        } catch (Exception $exception) {
            return $this->problem('Could not write to log file', [
                'exception' => $this->exceptionContext($exception),
            ]);
        }

        return $this->okay();
    }
}
