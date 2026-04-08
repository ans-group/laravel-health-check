<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Controllers;

class PingController
{
    public function __invoke(): string
    {
        return 'pong';
    }
}
