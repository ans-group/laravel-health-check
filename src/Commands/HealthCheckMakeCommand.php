<?php

namespace UKFast\HealthCheck\Commands;

use Illuminate\Console\GeneratorCommand;

class HealthCheckMakeCommand extends GeneratorCommand
{
    /**
     * The health check name.
     *
     * @var string
     */
    protected $signature = 'make:check {name : The name of the health check class}';

    /**
     * The check command description.
     *
     * @var string
     */
    protected $description = 'Create a new health check class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Check';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/Stubs/ExampleHealthCheck.php.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Checks';
    }
}
