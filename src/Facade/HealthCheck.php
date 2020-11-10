<?php

namespace UKFast\HealthCheck\Facade;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool passes(string $checkName)
 * @method static bool fails(string $checkName)
 * @method static Collection|\UKFast\HealthCheck\HealthCheck[] all()
 */
class HealthCheck extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'app-health';
    }
}
