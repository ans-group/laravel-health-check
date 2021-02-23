<?php

namespace Tests\Commands;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

class HealthCheckMakeCommandTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    /**
     * @test
     */
    public function creates_a_new_check()
    {
        $checkName = "TestCheck";
        $checkClassFile = $this->app->basePath("app/Checks/{$checkName}.php");

        $this->assertFalse(File::exists($checkClassFile));

        $this->artisan("make:check {$checkName}")->assertExitCode(0);

        $this->assertTrue(File::exists($checkClassFile));

        // Checking right file is created.
        $this->assertTrue(is_int(strpos(File::get($checkClassFile), "class {$checkName}")));

        // Cleaning the file.
        unlink($checkClassFile);
    }
}
