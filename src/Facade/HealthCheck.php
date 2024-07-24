<?php

namespace UKFast\HealthCheck\Facade;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use UKFast\HealthCheck\AppHealth;
use UKFast\HealthCheck\HealthCheck as Check;

/**
 * @method static bool passes(string $checkName)
 * @method static bool fails(string $checkName)
 * @method static Collection<int, Check> all()
 * @see AppHealth
 */
class HealthCheck extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'app-health';
    }
}
