<?php

declare(strict_types=1);

namespace Tests\Stubs\Checks;

use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class AlwaysUpCheck extends HealthCheck
{
    protected string $name = 'always-up';

    public function status(): Status
    {
        return $this->okay();
    }
}
