<?php

namespace UKFast\HealthCheck\Facade;

use Illuminate\Support\Facades\Facade;

class HealthCheck extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'app-health';
    }
}
