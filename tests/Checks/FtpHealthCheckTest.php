<?php

namespace Tests\Unit\HealthCheck;

use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\UnableToConnectToFtpHost;
use UKFast\HealthCheck\Checks\FtpHealthCheck;
use Tests\TestCase;
use Mockery;

class FtpHealthCheckTest extends TestCase
{
    public function testShowsProblemWhenCantConnectToFtpServer(): void
    {
        $ftp = Mockery::mock(FtpAdapter::class);
        $ftp->expects('listContents')
            ->andThrow(new  UnableToConnectToFtpHost('uwu'));

        $status = (new FtpHealthCheck($ftp))->status();

        $this->assertTrue($status->isProblem());

        Mockery::close();
    }

    public function testShowsOkayWhenCanConnectToFtpServer(): void
    {
        $generator = function (): iterable {
            yield 'foo';
            yield 'bar';
            yield 'baz';
        };

        $ftp = Mockery::mock(FtpAdapter::class);
        $ftp->expects('listContents')
            ->andReturn($generator())
            ->getMock();

        $status = (new FtpHealthCheck($ftp))->status();
        $this->assertTrue($status->isOkay());

        Mockery::close();
    }
}
