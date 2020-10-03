<?php

namespace Tests\Checks;

use Exception;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use UKFast\HealthCheck\Checks\StorageHealthCheck;

class StorageHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    /**
     * @test
     */
    public function shows_problem_if_cannot_write_to_storage()
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

    /**
     * @test
     */
    public function shows_problem_if_incorrect_read_from_storage()
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

    /**
     * @test
     */
    public function shows_okay_if_can_write_to_storage()
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

class BadDisk
{
    public function __call($name, $arguments)
    {
        throw new Exception();
    }
}
