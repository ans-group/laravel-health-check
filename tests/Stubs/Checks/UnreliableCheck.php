<?php

namespace Tests\Stubs\Checks;

use RuntimeException;
use UKFast\HealthCheck\HealthCheck;

class UnreliableCheck extends HealthCheck
{
    protected string $name = 'unreliable';

    /**
     * @throws RuntimeException
     */
    public function status(): never
    {
        throw new RuntimeException('Something went badly wrong');
    }
}
