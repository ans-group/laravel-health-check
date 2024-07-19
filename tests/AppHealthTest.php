<?php

namespace Tests;

use UKFast\HealthCheck\AppHealth;
use UKFast\HealthCheck\Exceptions\CheckNotFoundException;
use UKFast\HealthCheck\HealthCheck;

class AppHealthTest extends TestCase
{
    public function testCanSeeIfACheckPassesByName()
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck, new AlwaysDownCheck]));

        $this->assertTrue($appHealth->passes('always-up'));
        $this->assertFalse($appHealth->passes('always-down'));
    }

    public function testCanSeeIfACheckFailsByName()
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck, new AlwaysDownCheck]));

        $this->assertFalse($appHealth->fails('always-up'));
        $this->assertTrue($appHealth->fails('always-down'));
    }

    public function testReturnsFalseIfCheckThrowsException()
    {
        $appHealth = new AppHealth(collect([new UnreliableCheck]));

        $this->assertFalse($appHealth->passes('unreliable'));
    }

    public function testThrowsExceptionIfCheckDoesNotExist()
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck, new AlwaysDownCheck]));

        $this->expectException(CheckNotFoundException::class);

        $appHealth->passes('does-not-exist');
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


class UnreliableCheck extends HealthCheck
{
    protected $name = 'unreliable';

    public function status()
    {
        throw new \RuntimeException('Something went badly wrong');
    }
}