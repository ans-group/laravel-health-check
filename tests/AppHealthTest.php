<?php

namespace Tests;

use RuntimeException;
use UKFast\HealthCheck\AppHealth;
use UKFast\HealthCheck\Exceptions\CheckNotFoundException;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class AppHealthTest extends TestCase
{
    public function testCanSeeIfACheckPassesByName(): void
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck, new AlwaysDownCheck]));

        $this->assertTrue($appHealth->passes('always-up'));
        $this->assertFalse($appHealth->passes('always-down'));
    }

    public function testCanSeeIfACheckFailsByName(): void
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck, new AlwaysDownCheck]));

        $this->assertFalse($appHealth->fails('always-up'));
        $this->assertTrue($appHealth->fails('always-down'));
    }

    public function testReturnsFalseIfCheckThrowsException(): void
    {
        $appHealth = new AppHealth(collect([new UnreliableCheck]));

        $this->assertFalse($appHealth->passes('unreliable'));
    }

    public function testThrowsExceptionIfCheckDoesNotExist(): void
    {
        $appHealth = new AppHealth(collect([new AlwaysUpCheck, new AlwaysDownCheck]));

        $this->expectException(CheckNotFoundException::class);

        $appHealth->passes('does-not-exist');
    }
}

class AlwaysUpCheck extends HealthCheck
{
    protected string $name = 'always-up';

    public function status(): Status
    {
        return $this->okay();
    }
}

class AlwaysDownCheck extends HealthCheck
{
    protected string $name = 'always-down';

    public function status(): Status
    {
        return $this->problem('Something went wrong', [
            'debug' => 'info',
        ]);
    }
}


class UnreliableCheck extends HealthCheck
{
    protected string $name = 'unreliable';

    /**
     * @throws RuntimeException
     */
    public function status(): never
    {
        throw new RuntimeException('Something went badly wrong');
    }
}