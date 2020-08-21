<?php

namespace UKFast\HealthCheck\Exceptions;

use RuntimeException;

class CheckNotFoundException extends RuntimeException
{
    protected $name;

    public function __construct($name)
    {
        parent::__construct("No health check called '$name' exists");
    }

    public function getName()
    {
        return $this->name;
    }
}
