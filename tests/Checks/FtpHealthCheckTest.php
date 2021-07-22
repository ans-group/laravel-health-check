<?php

namespace Tests\Unit\HealthCheck;

use UKFast\HealthCheck\Checks\FtpHealthCheck;
use RuntimeException;
use League\Flysystem\Adapter\Ftp;
use Tests\TestCase;
use Mockery as m;

class FtpHealthCheckTest extends TestCase
{

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function shows_problem_when_cant_connect_to_ftp_server()
    {
        $ftp = m::mock(Ftp::class)
            ->expects('getConnection')
            ->andThrow(new RuntimeException('uwu'))
            ->getMock();

        $status = (new FtpHealthCheck($ftp))->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_okay_when_can_connect_to_ftp_server()
    {
        $ftp = m::mock(Ftp::class)
            ->expects('getConnection')
            ->andReturn(true)
            ->getMock();

        $status = (new FtpHealthCheck($ftp))->status();
        $this->assertTrue($status->isOkay());
    }
}
