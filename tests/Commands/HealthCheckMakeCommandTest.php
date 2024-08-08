<?php

namespace Tests\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use Tests\TestCase;

class HealthCheckMakeCommandTest extends TestCase
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

    public function testCreatesANewCheck(): void
    {
        $checkName = "TestCheck";
        $checkClassFile = $this->app->basePath("app/Checks/{$checkName}.php");

        $this->assertFalse(File::exists($checkClassFile));

        $this->artisan("make:check", ["name" => $checkName]);

        $this->assertTrue(File::exists($checkClassFile));

        // Checking right file is created.
        $this->assertTrue(is_int(strpos(File::get($checkClassFile), "class {$checkName}")));

        // Cleaning the file.
        unlink($checkClassFile);
    }

    public function testPhpReservedNameCheckDoesNotGetCreated(): void
    {
        if (!property_exists(GeneratorCommand::class, 'reservedNames')) {
            $this->markTestSkipped('GeneratorCommand does not support reservedNames.');
        }

        $checkName = "array";
        $checkClassFile = $this->app->basePath("app/Checks/{$checkName}.php");
        $this->assertFalse(File::exists($checkClassFile));

        $this->artisan("make:check", ["name" => $checkName]);

        $this->assertFalse(File::exists($checkClassFile));
    }
}
