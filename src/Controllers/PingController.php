<?php

namespace UKFast\HealthCheck\Controllers;

class PingController
{
    public function __invoke(): string
    {
        return 'pong';
    }
}
