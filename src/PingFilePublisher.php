<?php

declare(strict_types=1);

namespace UKFast\HealthCheck;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class PingFilePublisher
{
    /**
     * Copies the ping stub to `public/{path}` if it isn't already present.
     * Never overwrites an existing file.
     */
    public function publish(string $relativePath): void
    {
        if (! function_exists('public_path')) {
            return;
        }

        $relative = trim($relativePath, '/');

        $hasUnsafeSegment = collect(explode('/', $relative))
            ->contains(fn(string $segment): bool => $segment === '' || $segment === '..');

        if ($hasUnsafeSegment) {
            throw new InvalidArgumentException(
                "healthcheck.ping.path [{$relative}] must be a relative path with no \"..\" segments."
            );
        }

        $publicPath = realpath(public_path());

        if ($publicPath === false) {
            throw new InvalidArgumentException('The public directory could not be resolved.');
        }

        $publicPath = rtrim($publicPath, '/');
        $target = $publicPath . '/' . $relative;

        if (File::exists($target)) {
            return;
        }

        File::ensureDirectoryExists(dirname($target));

        $resolvedDirectory = (string) realpath(dirname($target));

        if ($resolvedDirectory !== $publicPath && ! str_starts_with($resolvedDirectory, $publicPath . '/')) {
            throw new InvalidArgumentException(
                "healthcheck.ping.path [{$relative}] must resolve to a location under the public directory."
            );
        }

        File::copy(__DIR__ . '/../stubs/ping', $target);
    }
}
