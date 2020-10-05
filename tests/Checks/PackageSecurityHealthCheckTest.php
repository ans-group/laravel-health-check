<?php

namespace Tests\Checks;

use Exception;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\PackageSecurityHealthCheck;

class PackageSecurityHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    /**
     * @test
     */
    public function shows_problem_if_required_package_not_loaded()
    {
        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_problem_if_cannot_check_packages()
    {
        $this->partialMock('overload:SensioLabs\Security\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')->andThrow(new Exception('Lock file does not exist.'));
        });

        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_problem_if_package_has_vulnerability()
    {
        $this->partialMock('overload:SensioLabs\Security\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')
                ->andReturn(new MockResult(1, file_get_contents('tests/json/sensiolabsPackageHasVulnerability.json')));
        });

        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function ignores_package_if_in_config()
    {
        config([
            'healthcheck.package-security.ignore' => [
                'example/package',
            ],
        ]);

        $this->partialMock('overload:SensioLabs\Security\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')
                ->andReturn(new MockResult(1, file_get_contents('tests/json/sensiolabsPackageHasVulnerability.json')));
        });

        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isOkay());
    }

    /**
     * @test
     */
    public function shows_okay_if_no_packages_have_vulnerabilities()
    {
        $this->partialMock('overload:SensioLabs\Security\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')
                ->andReturn(new MockResult(0, '{}'));
        });

        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isOkay());
    }
}

class MockResult
{
    private $vulnerabilities;

    public function __construct($count, $vulnerabilities)
    {
        $this->count = $count;
        $this->vulnerabilities = $vulnerabilities;
    }

    public function __toString()
    {
        return $this->vulnerabilities;
    }

    public function count()
    {
        return $this->count;
    }
}
