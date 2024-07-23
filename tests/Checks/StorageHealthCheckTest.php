<?php

namespace Tests\Checks;

use Illuminate\Foundation\Application;
use Tests\Stubs\Storage\BadDisk;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use UKFast\HealthCheck\Checks\StorageHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class StorageHealthCheckTest extends TestCase
{
    /**
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array
    {
        return [HealthCheckServiceProvider::class];
    }

    public function testShowsProblemIfCannotWriteToStorage(): void
    {
        config([
            'healthcheck.storage.disks' => [
                'local'
            ]
        ]);

        Storage::shouldReceive('disk')->andReturn(new BadDisk());

        $status = (new StorageHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsProblemIfIncorrectReadFromStorage(): void
    {
        config([
            'healthcheck.storage.disks' => [
                'local'
            ]
        ]);

        Storage::shouldReceive('disk')->with('local')->once()->andReturnSelf()
            ->shouldReceive('put')->once()
            ->shouldReceive('get')->once()->andReturn('incorrect-string')
            ->shouldReceive('delete')->once();

        $status = (new StorageHealthCheck($this->app))->status();

        $this->assertTrue($status->isProblem());
    }

    public function test_shows_okay_if_can_write_to_storage(): void
    {
        config([
            'healthcheck.storage.disks' => [
                'local'
            ]
        ]);

        $status = (new StorageHealthCheck($this->app))->status();

        $this->assertTrue($status->isOkay());
    }
}
