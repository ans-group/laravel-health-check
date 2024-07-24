<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Support\Collection;
use Enlightn\SecurityChecker\SecurityChecker;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class PackageSecurityHealthCheck extends HealthCheck
{
    protected string $name = 'package-security';

    /**
     * @var Collection<int, string> $vulnerablePackages
     */
    protected Collection $vulnerablePackages;

    public function __construct()
    {
        $this->vulnerablePackages = collect();
    }

    /**
     * @param class-string $class
     */
    public static function checkDependency(string $class): bool
    {
        return class_exists($class);
    }

    public function status(): Status
    {
        try {
            if (! static::checkDependency(SecurityChecker::class)) {
                if (static::checkDependency('SensioLabs\Security\SecurityChecker')) {
                    throw new Exception(
                        'The sensiolabs/security-checker package has been archived.'
                            . ' Install enlightn/security-checker instead.'
                    );
                }
                throw new Exception('You need to install the enlightn/security-checker package to use this check.');
            }

            $checker = new SecurityChecker();
            $result = $checker->check(
                base_path('composer.lock'),
                config('healthcheck.package-security.exclude-dev', false),
            );

            $vulnerabilities = collect($result);

            if ($vulnerabilities->isNotEmpty()) {
                $this->vulnerablePackages = $vulnerabilities->reject(
                    fn($vulnerability, $package): bool =>
                        in_array($package, config('healthcheck.package-security.ignore'))
                );

                if ($this->vulnerablePackages->count()) {
                    $this->vulnerablePackages->transform(
                        fn($vulnerability, $package): string => $vulnerability['version']
                    );

                    return $this->problem(
                        'Some packages have security vulnerabilities',
                        [
                            'packages' => $this->vulnerablePackages->toArray(),
                        ]
                    );
                }
            }
        } catch (Exception $e) {
            return $this->problem('Failed to check packages for security vulnerabilities', [
                'exception' => $this->exceptionContext($e),
            ]);
        }

        return $this->okay();
    }
}
