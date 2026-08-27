<?php

declare(strict_types=1);

namespace Tests\Stubs\Checks;

use UKFast\HealthCheck\HealthCheck;

class AlwaysUpCheck extends HealthCheck
{
    protected string $name = 'always-up';

    public function check(): void
    {
    }
}
