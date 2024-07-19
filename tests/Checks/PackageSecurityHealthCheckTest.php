<?php

namespace Tests\Checks;

use Closure;
use Exception;
use Illuminate\Foundation\Application;
use Mockery;
use Mockery\MockInterface;
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
        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    public function shows_problem_if_cannot_check_packages(): void
    {
        $this->partialMock('overload:SensioLabs\Security\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')->andThrow(new Exception('Lock file does not exist.'));
        });

        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsProblemIfPackageHasVulnerability(): void
    {
        $this->partialMock('overload:SensioLabs\Security\SecurityChecker', function ($mock) {
            $mock->shouldReceive('check')
                ->andReturn(new MockResult(1, file_get_contents('tests/json/sensiolabsPackageHasVulnerability.json')));
        });

        $status = (new PackageSecurityHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    public function testIgnoresPackageIfInConfig(): void
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

    public function testShowsOkayIfNoPackagesHaveVulnerabilities(): void
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
    public function __construct(
        public readonly int $count,
        private readonly string $vulnerabilities,
    ) {
    }

    public function __toString(): string
    {
        return $this->vulnerabilities;
    }

    public function count(): int
    {
        return $this->count;
    }
}
