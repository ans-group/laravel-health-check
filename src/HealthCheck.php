<?php

namespace UKFast\HealthCheck;

use Exception;

abstract class HealthCheck
{
    /** @return string */
    protected $name;

    /** @return Status */
    abstract public function status();

    public function name()
    {
        return $this->name;
    }

    public function problem($message = '', $context = [])
    {
        return (new Status)
            ->problem($message)
            ->withContext($context)
            ->withName($this->name());
    }

    public function okay($context = [])
    {
        return (new Status)
            ->okay()
            ->withContext($context)
            ->withName($this->name());
    }

    protected function exceptionContext(Exception $e)
    {
        return [
            'error' => $e->getMessage(),
            'class' => get_class($e),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => explode("\n", $e->getTraceAsString()),
        ];
    }
}
