<?php

namespace Tests\Stubs\Checks;

use Enlightn\SecurityChecker\SecurityChecker;
use UKFast\HealthCheck\Checks\PackageSecurityHealthCheck as BasePackageSecurityHealthCheck;

class PackageSecurityHealthCheck extends BasePackageSecurityHealthCheck
{
    /**
     * @var array<string, bool>
     */
    public static array $classResults = [
        SecurityChecker::class => false,
        'SensioLabs\Security\SecurityChecker' => false,
    ];

    public static function checkDependency(string $class): bool
    {
        return static::$classResults[$class];
    }
}
