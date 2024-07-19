<?php

namespace Tests\Checks;

use Closure;
use Exception;
use Mockery;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\PackageSecurityHealthCheck;

class PackageSecurityHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    /**
     * Mock a partial instance of an object in the container.
     *
     * @param  string  $abstract
     * @param  \Closure|null  $mock
     * @return \Mockery\MockInterface
     */
    protected function partialMock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args()))->makePartial());
    }

    public function testShowsProblemIfRequiredPackageNotLoaded()
    {
        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    public function shows_problem_if_cannot_check_packages()
    {
        $this->partialMock('overload:SensioLabs\Security\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')->andThrow(new Exception('Lock file does not exist.'));
        });

        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsProblemIfPackageHasVulnerability()
    {
        $this->partialMock('overload:SensioLabs\Security\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')
                ->andReturn(new MockResult(1, file_get_contents('tests/json/sensiolabsPackageHasVulnerability.json')));
        });

        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    public function testIgnoresPackageIfInConfig()
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

    public function testShowsOkayIfNoPackagesHaveVulnerabilities()
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
