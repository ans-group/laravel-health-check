<?php

declare(strict_types=1);

namespace Tests\Stubs\Checks;

use UKFast\HealthCheck\HealthCheck;

class AlwaysDownCheck extends HealthCheck
{
    protected string $name = 'always-down';

    public function check(): void
    {
        $this->fail('Something went wrong', [
            'debug' => 'info',
        ]);
    }
}
