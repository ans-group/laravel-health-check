<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use Enlightn\SecurityChecker\SecurityChecker;
use UKFast\HealthCheck\HealthCheck;

class PackageSecurityHealthCheck extends HealthCheck
{
    protected $name = 'package-security';

    protected $vulnerablePackages = [];

    public static function checkDependency($class)
    {
        return class_exists($class);
    }

    public function status()
    {
        try {
            if (! static::checkDependency(SecurityChecker::class)) {
                if (static::checkDependency(\SensioLabs\Security\SecurityChecker::class)) {
                    throw new Exception('The sensiolabs/security-checker package has been archived. Install enlightn/security-checker instead.');
                }
                throw new Exception('You need to install the enlightn/security-checker package to use this check.');
            }

            $checker = new SecurityChecker();
            $result = $checker->check(
                base_path('composer.lock'),
                config('healthcheck.package-security.exclude-dev', false)
            );

            $vulnerabilities = collect($result);

            if ($vulnerabilities->count() > 0) {
                $this->vulnerablePackages = $vulnerabilities->reject(function ($vulnerability, $package) {
                    return in_array($package, config('healthcheck.package-security.ignore'));
                });

                if ($this->vulnerablePackages->count()) {
                    $this->vulnerablePackages->transform(function ($vulnerability, $package) {
                        return $vulnerability['version'];
                    });

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
