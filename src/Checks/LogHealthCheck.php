<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Contracts\Container\Container;
use UKFast\HealthCheck\HealthCheck;

class LogHealthCheck extends HealthCheck
{
    protected $name = 'log';

    protected $logger;

    public function __construct(Container $container)
    {
        $this->logger = $container->make('log');
    }

    public function status()
    {
        try {
            $this->logger->info('Checking if logs are writable');
        } catch (Exception $e) {
            return $this->problem('Could not write to log file', [
                'exception' => $this->exceptionContext($e),
            ]);
        }

        return $this->okay();
    }
}