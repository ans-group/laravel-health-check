<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Support\Collection;
use SensioLabs\Security\SecurityChecker;
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

    public static function checkDependency(): bool
    {
        return class_exists(SecurityChecker::class);
    }

    public function status(): Status
    {
        try {
            if (! static::checkDependency()) {
                throw new Exception('You need to install the sensiolabs/security-checker package to use this check.');
            }

            $checker = new SecurityChecker();
            $result = $checker->check(base_path('composer.lock'), 'json');

            if ($result->count() > 0) {
                $vulnerabilities = collect(json_decode((string) $result, true));
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
