<?php

namespace Tests\Stubs\Checks;

use UKFast\HealthCheck\Checks\PackageSecurityHealthCheck as BasePackageSecurityHealthCheck;

class PackageSecurityHealthCheck extends BasePackageSecurityHealthCheck
{
    /**
     * @var array<string, bool>
     */
    public static array $classResults = [
        'Enlightn\SecurityChecker\SecurityChecker' => false,
        'SensioLabs\Security\SecurityChecker' => false,
    ];

    public static function checkDependency(string $class): bool
    {
        return static::$classResults[$class];
    }
}
