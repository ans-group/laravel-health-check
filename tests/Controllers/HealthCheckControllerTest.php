<?php

namespace Tests\Controllers;

use Tests\TestCase;
use UKFast\HealthCheck\Controllers\HealthCheckController;
use UKFast\HealthCheck\HealthCheck;

class HealthCheckControllerTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }
    
    /**
     * @test
     */
    public function returns_overall_status_of_okay_when_everything_is_up()
    {
        $this->setChecks([AlwaysUpCheck::class]);
        $response = (new HealthCheckController)->__invoke($this->app);

        $this->assertEquals([
            'status' => 'OK',
            'always-up' => ['status' => 'OK'],
        ], json_decode($response->getContent(), true));
    }

    /**
     * @test
     */
    public function returns_status_of_problem_when_a_problem_occurs()
    {
        $this->setChecks([AlwaysUpCheck::class, AlwaysDownCheck::class]);
        $response = (new HealthCheckController)->__invoke($this->app);

        $this->assertEquals([
            'status' => 'PROBLEM',
            'always-up' => ['status' => 'OK'],
            'always-down' => [
                'status' => 'PROBLEM',
                'message' => 'Something went wrong',
                'context' => ['debug' => 'info']
            ]
        ], json_decode($response->getContent(), true));
    }

    protected function setChecks($checks)
    {
        config(['healthcheck.checks' => $checks]);
    }
}

class AlwaysUpCheck extends HealthCheck
{
    protected $name = 'always-up';

    public function status()
    {
        return $this->okay();
    }
}

class AlwaysDownCheck extends HealthCheck
{
    protected $name = 'always-down';

    public function status()
    {
        return $this->problem('Something went wrong', [
            'debug' => 'info',
        ]);
    }
}