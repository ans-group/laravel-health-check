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

    /**
     * @test
     */
    public function shows_problem_if_required_package_not_loaded()
    {
        $status = (new MockedPackageSecurityHealthCheck)->status();

        $this->assertTrue($status->isProblem());
        $this->assertSame('You need to install the enlightn/security-checker package to use this check.', $status->context()['exception']['error']);
    }

    /**
     * @test
     */
    public function shows_problem_if_incorrect_package_loaded()
    {
        MockedPackageSecurityHealthCheck::$classResults = [
            'Enlightn\SecurityChecker\SecurityChecker' => false,
            'SensioLabs\Security\SecurityChecker' => true,
        ];
        $status = (new MockedPackageSecurityHealthCheck)->status();

        $this->assertTrue($status->isProblem());
        $this->assertSame('The sensiolabs/security-checker package has been archived. Install enlightn/security-checker instead.', $status->context()['exception']['error']);
    }

    /**
     * @test
     */
    public function shows_problem_if_cannot_check_packages()
    {
        $this->partialMock('overload:Enlightn\SecurityChecker\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')->andThrow(new Exception('File not found at [/tmp/composer.lock]'));
        });

        $status = (new PackageSecurityHealthCheck)->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_problem_if_package_has_vulnerability()
    {
        $this->partialMock('overload:Enlightn\SecurityChecker\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')
                ->andReturn(json_decode(file_get_contents('tests/json/securityCheckerPackageHasVulnerability.json'), true));
        });

        $status = (new PackageSecurityHealthCheck)->status();

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

        $this->partialMock('overload:Enlightn\SecurityChecker\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')
                ->andReturn(json_decode(file_get_contents('tests/json/securityCheckerPackageHasVulnerability.json'), true));
        });

        $status = (new PackageSecurityHealthCheck)->status();

        $this->assertTrue($status->isOkay());
    }

    /**
     * @test
     */
    public function shows_okay_if_no_packages_have_vulnerabilities()
    {
        $this->partialMock('overload:Enlightn\SecurityChecker\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')
                ->andReturn([]);
        });

        $status = (new PackageSecurityHealthCheck)->status();

        $this->assertTrue($status->isOkay());
    }
}

class MockedPackageSecurityHealthCheck extends \UKFast\HealthCheck\Checks\PackageSecurityHealthCheck
{
    public static $classResults = [
        'Enlightn\SecurityChecker\SecurityChecker' => false,
        'SensioLabs\Security\SecurityChecker' => false,
    ];

    public static function checkDependency($class)
    {
        return static::$classResults[$class];
    }
}
