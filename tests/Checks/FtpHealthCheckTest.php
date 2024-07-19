<?php

namespace Tests\Unit\HealthCheck;

use UKFast\HealthCheck\Checks\FtpHealthCheck;
use RuntimeException;
use League\Flysystem\Adapter\Ftp;
use Tests\TestCase;
use Mockery as m;

class FtpHealthCheckTest extends TestCase
{
    public function testShowsProblemWhenCantConnectToFtpServer(): void
    {
        $ftp = m::mock(Ftp::class)
            ->expects('getConnection')
            ->andThrow(new RuntimeException('uwu'))
            ->getMock();

        $status = (new FtpHealthCheck($ftp))->status();

        $this->assertTrue($status->isProblem());

        m::close();
    }

    public function testShowsOkayWhenCanConnectToFtpServer(): void
    {
        $ftp = m::mock(Ftp::class)
            ->expects('getConnection')
            ->andReturn(true)
            ->getMock();

        $status = (new FtpHealthCheck($ftp))->status();
        $this->assertTrue($status->isOkay());

        m::close();
    }
}
