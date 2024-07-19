<?php

namespace Tests;

use UKFast\HealthCheck\Status;

class StatusTest extends TestCase
{
    public function testCanCreateAnOkayStatus()
    {
        $status = (new Status)->okay();

        $this->assertTrue($status->isOkay());
        $this->assertFalse($status->isProblem());
    }

    public function testCanCreateAProblemStatus()
    {
        $status = (new Status)->problem();

        $this->assertTrue($status->isProblem());
        $this->assertFalse($status->isOkay());
    }

    public function testCanInjectAContext()
    {
        $status = (new Status)->withContext('arbitrary context');

        $this->assertSame('arbitrary context', $status->context());
    }

    public function testCanSetANameForAStatus()
    {
        $status = (new Status)->withName('redis');

        $this->assertSame('redis', $status->name());
    }

    public function testWhenCreatingAProblemCanAttachAMessage()
    {
        $status = (new Status)->problem('My thing doesnt work');

        $this->assertSame('My thing doesnt work', $status->message());
    }
}
