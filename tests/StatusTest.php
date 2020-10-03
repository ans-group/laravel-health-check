<?php

namespace Tests;

use UKFast\HealthCheck\Status;

class StatusTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_an_okay_status()
    {
        $status = (new Status)->okay();

        $this->assertTrue($status->isOkay());
        $this->assertFalse($status->isProblem());
    }

    /**
     * @test
     */
    public function can_create_a_problem_status()
    {
        $status = (new Status)->problem();

        $this->assertTrue($status->isProblem());
        $this->assertFalse($status->isOkay());
    }

    /**
     * @test
     */
    public function can_inject_a_context()
    {
        $status = (new Status)->withContext('arbitrary context');

        $this->assertSame('arbitrary context', $status->context());
    }

    /**
     * @test
     */
    public function can_set_a_name_for_a_status()
    {
        $status = (new Status)->withName('redis');

        $this->assertSame('redis', $status->name());
    }

    /**
     * @test
     */
    public function when_creating_a_problem_can_attach_a_message()
    {
        $status = (new Status)->problem('My thing doesnt work');

        $this->assertSame('My thing doesnt work', $status->message());
    }
}
