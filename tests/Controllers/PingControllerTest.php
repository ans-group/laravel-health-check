<?php

namespace Tests\Controllers;

use Tests\TestCase;
use UKFast\HealthCheck\Controllers\PingController;

class PingControllerTest extends TestCase
{
    /**
     * @test
     */
    public function returns_pong()
    {
        $this->assertSame('pong', (new PingController)->__invoke());
    }
}
