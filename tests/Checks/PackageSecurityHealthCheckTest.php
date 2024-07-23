<?php

namespace Tests\Checks;

use Closure;
use Exception;
use Illuminate\Foundation\Application;
use Mockery;
use Mockery\MockInterface;
use Tests\Stubs\Checks\PackageSecurityHealthCheck as StubPackageSecurityHealthCheck;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\PackageSecurityHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class PackageSecurityHealthCheckTest extends TestCase
{
    /**
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array
    {
        return [HealthCheckServiceProvider::class];
    }

    /**
     * Mock a partial instance of an object in the container.
     *
     * @param  string  $abstract
     * @param  \Closure|null  $mock
     * @return MockInterface
     */
    protected function partialMock($abstract, Closure $mock = null): MockInterface
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args()))->makePartial());
    }

    public function testShowsProblemIfRequiredPackageNotLoaded()
    {
        $status = (new StubPackageSecurityHealthCheck())->status();

        $this->assertTrue($status->isProblem());
        $this->assertSame('You need to install the enlightn/security-checker package to use this check.', $status->context()['exception']['error']);
    }

    public function testShowsProblemIfIncorrectPackageLoaded(): void
    {
        StubPackageSecurityHealthCheck::$classResults = [
            'Enlightn\SecurityChecker\SecurityChecker' => false,
            'SensioLabs\Security\SecurityChecker' => true,
        ];
        $status = (new StubPackageSecurityHealthCheck())->status();

        $this->assertTrue($status->isProblem());
        $this->assertSame('The sensiolabs/security-checker package has been archived. Install enlightn/security-checker instead.', $status->context()['exception']['error']);
    }

    public function testShowsProblemIfCannotCheckPackages(): void
    {
        $this->partialMock('overload:Enlightn\SecurityChecker\SecurityChecker', fn (MockInterface $mock) =>
            $mock->shouldReceive('check')->andThrow(new Exception('File not found at [/tmp/composer.lock]')));

        $status = (new PackageSecurityHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsProblemIfPackageHasVulnerability(): void
    {
        $this->partialMock('overload:Enlightn\SecurityChecker\SecurityChecker', fn (MockInterface $mock) =>
            $mock->shouldReceive('check')
                ->andReturn(json_decode(file_get_contents('tests/json/securityCheckerPackageHasVulnerability.json'), true)));

        $status = (new PackageSecurityHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    public function testIgnoresPackageIfInConfig(): void
    {
        config([
            'healthcheck.package-security.ignore' => [
                'example/package',
            ],
        ]);

        $this->partialMock('overload:Enlightn\SecurityChecker\SecurityChecker', fn (MockInterface $mock) =>
            $mock->shouldReceive('check')
                ->andReturn(json_decode(file_get_contents('tests/json/securityCheckerPackageHasVulnerability.json'), true)));

        $status = (new PackageSecurityHealthCheck())->status();

        $this->assertTrue($status->isOkay());
    }

    public function testShowsOkayIfNoPackagesHaveVulnerabilities(): void
    {
        $this->partialMock('overload:Enlightn\SecurityChecker\SecurityChecker', fn(MockInterface $mock) =>
            $mock->shouldReceive('check')
                ->andReturn([]));

        $status = (new PackageSecurityHealthCheck())->status();

        $this->assertTrue($status->isOkay());
    }
}
