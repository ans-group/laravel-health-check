<?php

namespace Tests;

use UKFast\HealthCheck\AppHealth;
use UKFast\HealthCheck\HealthCheck;

class AppHealthTest extends TestCase
{
    /**
     * @test
     */
    public function can_see_if_a_check_passes_by_name()
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck, new AlwaysDownCheck]));

        $this->assertTrue($appHealth->passes('always-up'));
        $this->assertFalse($appHealth->passes('always-down'));
    }

    /**
     * @test
     */
    public function can_see_if_a_check_fails_by_name()
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck, new AlwaysDownCheck]));

        $this->assertFalse($appHealth->fails('always-up'));
        $this->assertTrue($appHealth->fails('always-down'));   
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