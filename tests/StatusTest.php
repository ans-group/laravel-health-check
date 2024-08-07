<?php

namespace Tests;

use UKFast\HealthCheck\Status;

class StatusTest extends TestCase
{
    public function testCanCreateAnOkayStatus(): void
    {
        $status = (new Status())->okay();

        $this->assertTrue($status->isOkay());
        $this->assertFalse($status->isProblem());
    }

    public function testCanCreateAProblemStatus(): void
    {
        $status = (new Status())->problem();

        $this->assertTrue($status->isProblem());
        $this->assertFalse($status->isOkay());
    }

    public function testCanInjectAContext(): void
    {
        $status = (new Status())->withContext([
            'context' => 'arbitrary context',
        ]);

        $this->assertSame(
            [
                'context' => 'arbitrary context',
            ],
            $status->context()
        );
    }

    public function testCanSetANameForAStatus(): void
    {
        $status = (new Status())->withName('redis');

        $this->assertSame('redis', $status->name());
    }

    public function testWhenCreatingAProblemCanAttachAMessage(): void
    {
        $status = (new Status())->problem('My thing doesnt work');

        $this->assertSame('My thing doesnt work', $status->message());
    }
}
