<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
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

    public function testDoesNotPublishAPingFileByDefault(): void
    {
        $this->assertFalse(File::exists(public_path('ping')));
        $this->get('/ping')->assertNotFound();
    }

    public function testPublishesAPingFileWhenEnabled(): void
    {
        $target = public_path('ping');
        $this->forgetPingFile($target);

        config(['healthcheck.ping.enabled' => true]);

        /** @var HealthCheckServiceProvider $provider */
        $provider = $this->app->getProvider(HealthCheckServiceProvider::class);
        $provider->boot();

        $this->assertTrue(File::exists($target));
        $this->assertSame("pong\n", File::get($target));

        $this->forgetPingFile($target);
    }

    public function testDoesNotOverwriteAnExistingPingFile(): void
    {
        $target = public_path('ping');
        File::put($target, "custom\n");

        config(['healthcheck.ping.enabled' => true]);

        /** @var HealthCheckServiceProvider $provider */
        $provider = $this->app->getProvider(HealthCheckServiceProvider::class);
        $provider->boot();

        $this->assertSame("custom\n", File::get($target));

        $this->forgetPingFile($target);
    }

    public function testRejectsAPingPathThatTraversesOutsideThePublicDirectory(): void
    {
        config([
            'healthcheck.ping.enabled' => true,
            'healthcheck.ping.path' => '../../etc/cron.d/malicious',
        ]);

        /** @var HealthCheckServiceProvider $provider */
        $provider = $this->app->getProvider(HealthCheckServiceProvider::class);

        $this->expectException(InvalidArgumentException::class);

        $provider->boot();
    }

    public function testPublishesWhenThePublicPathIsASymlinkToAnotherDirectory(): void
    {
        $root = sys_get_temp_dir() . '/health-check-ping-symlink-test';
        $releasesDir = $root . '/release-1/public';
        $currentLink = $root . '/current';

        File::deleteDirectory($root);
        File::ensureDirectoryExists($releasesDir);
        symlink($releasesDir, $currentLink);

        try {
            $this->app->usePublicPath($currentLink);

            config(['healthcheck.ping.enabled' => true]);

            /** @var HealthCheckServiceProvider $provider */
            $provider = $this->app->getProvider(HealthCheckServiceProvider::class);
            $provider->boot();

            $this->assertTrue(File::exists($releasesDir . '/ping'));
            $this->assertSame("pong\n", File::get($releasesDir . '/ping'));
        } finally {
            File::deleteDirectory($root);
        }
    }

    public function testPingStubContainsPong(): void
    {
        $this->assertSame("pong\n", File::get(dirname(__DIR__) . '/stubs/ping'));
    }

    private function forgetPingFile(string $target): void
    {
        if (File::exists($target)) {
            File::delete($target);
        }
    }
}
