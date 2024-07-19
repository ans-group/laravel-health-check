<?php

namespace Tests\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use Tests\TestCase;

class HealthCheckMakeCommandTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return [HealthCheckServiceProvider::class];
    }

    public function testCreatesANewCheck()
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

    public function testPhpReservedNameCheckDoesNotGetCreated()
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
