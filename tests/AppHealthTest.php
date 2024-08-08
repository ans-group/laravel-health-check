<?php

namespace Tests;

use Tests\Stubs\Checks\AlwaysDownCheck;
use Tests\Stubs\Checks\AlwaysUpCheck;
use Tests\Stubs\Checks\UnreliableCheck;
use UKFast\HealthCheck\AppHealth;
use UKFast\HealthCheck\Exceptions\CheckNotFoundException;

class AppHealthTest extends TestCase
{
    public function testCanSeeIfACheckPassesByName(): void
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck(), new AlwaysDownCheck()]));

        $this->assertTrue($appHealth->passes('always-up'));
        $this->assertFalse($appHealth->passes('always-down'));
    }

    public function testCanSeeIfACheckFailsByName(): void
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck(), new AlwaysDownCheck()]));

        $this->assertFalse($appHealth->fails('always-up'));
        $this->assertTrue($appHealth->fails('always-down'));
    }

    public function testReturnsFalseIfCheckThrowsException(): void
    {
        $appHealth = new AppHealth(collect([new UnreliableCheck()]));

        $this->assertFalse($appHealth->passes('unreliable'));
    }

    public function testThrowsExceptionIfCheckDoesNotExist(): void
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck(), new AlwaysDownCheck()]));

        $this->expectException(CheckNotFoundException::class);

        $appHealth->passes('does-not-exist');
    }
}
