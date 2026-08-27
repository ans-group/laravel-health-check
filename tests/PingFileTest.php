<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class PingFileTest extends TestCase
{
    /**
     * @inheritDoc
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array
    {
        return [HealthCheckServiceProvider::class];
    }

    public function testDoesNotRegisterAPingRouteOrFileByDefault(): void
    {
        $this->assertFalse(File::exists(public_path('ping')));
        $this->get('/ping')->assertNotFound();
    }

    public function testPingStubContainsPong(): void
    {
        $this->assertSame("pong\n", File::get(dirname(__DIR__) . '/stubs/ping'));
    }

    public function testStubIsRegisteredUnderTheHealthcheckPingPublishTag(): void
    {
        $paths = HealthCheckServiceProvider::pathsToPublish(
            HealthCheckServiceProvider::class,
            'healthcheck-ping',
        );

        $this->assertCount(1, $paths);
        $this->assertSame(
            realpath(dirname(__DIR__) . '/stubs/ping'),
            realpath(array_key_first($paths)),
        );
        $this->assertSame(public_path('ping'), array_values($paths)[0]);
    }
}
