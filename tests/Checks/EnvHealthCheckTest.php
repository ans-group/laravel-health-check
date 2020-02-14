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

    /**
     * @test
     */
    public function shows_problem_if_missing_a_dotenv_file()
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

    /**
     * @test
     */
    public function shows_okay_if_all_required_env_params_are_present()
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
}
