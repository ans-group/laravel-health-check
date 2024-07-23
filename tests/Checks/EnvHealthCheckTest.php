<?php

namespace Tests\Checks;

use Illuminate\Foundation\Application;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\EnvHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class EnvHealthCheckTest extends TestCase
{
    /**
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array
    {
        return [HealthCheckServiceProvider::class];
    }

    public function testShowsProblemIfMissingADotenvFile(): void
    {
        putenv('REDIS_HOST=here');
        putenv('MYSQL_HOST=here');

        config(['healthcheck.required-env' => [
            'REDIS_HOST',
            'MYSQL_PASSWORD'
        ]]);
        $status = (new EnvHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }
    function testShowsOkayIfAllRequiredEnvParamsArePresent(): void
    {
        putenv('REDIS_HOST=here');
        putenv('MYSQL_HOST=here');
        putenv('MYSQL_PASSWORD=here');

        config(['healthcheck.required-env' => [
            'REDIS_HOST',
            'MYSQL_PASSWORD'
        ]]);
           $status = (new EnvHealthCheck())->status();

           $this->assertTrue($status->isOkay());
    }

    public function testShowsOkayIfRequiredEnvParamIsPresentButNull(): void
    {
        putenv('REDIS_PASSWORD=null');

        config(['healthcheck.required-env' => [
            'REDIS_PASSWORD',
        ]]);
        $status = (new EnvHealthCheck())->status();

        $this->assertTrue($status->isOkay());
    }
}
