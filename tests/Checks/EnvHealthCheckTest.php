<?php

namespace Tests\Checks;

use Tests\TestCase;
use UKFast\HealthCheck\Checks\EnvHealthCheck;

class EnvHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    public function testShowsProblemIfMissingADotenvFile()
    {
        putenv('REDIS_HOST=here');
        putenv('MYSQL_HOST=here');

        config(['healthcheck.required-env' => [
            'REDIS_HOST',
            'MYSQL_PASSWORD'
        ]]);
        $status = (new EnvHealthCheck)->status();

        $this->assertTrue($status->isProblem());
    }
 function testShowsOkayIfAllRequiredEnvParamsArePresent()
    {
        putenv('REDIS_HOST=here');
        putenv('MYSQL_HOST=here');
        putenv('MYSQL_PASSWORD=here');

        config(['healthcheck.required-env' => [
            'REDIS_HOST',
            'MYSQL_PASSWORD'
        ]]);
        $status = (new EnvHealthCheck)->status();

        $this->assertTrue($status->isOkay());
    }

    public function testShowsOkayIfRequiredEnvParamIsPresentButNull()
    {
        putenv('REDIS_PASSWORD=null');

        config(['healthcheck.required-env' => [
            'REDIS_PASSWORD',
        ]]);
        $status = (new EnvHealthCheck)->status();

        $this->assertTrue($status->isOkay());
    }
}
