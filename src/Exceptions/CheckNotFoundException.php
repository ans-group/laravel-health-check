<?php

namespace UKFast\HealthCheck\Exceptions;

use RuntimeException;

class CheckNotFoundException extends RuntimeException
{
    protected string $name;

    public function __construct(string $name)
    {
        parent::__construct("No health check called '$name' exists");
    }

    public function getName(): string
    {
        return $this->name;
    }
}
