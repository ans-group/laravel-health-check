<?php

declare(strict_types=1);

namespace Tests;

use UKFast\HealthCheck\Exceptions\HealthCheckDegradedException;
use UKFast\HealthCheck\Exceptions\HealthCheckFailedException;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.debug', false);
    }

    protected function runCheck(HealthCheck $check): Status
    {
        try {
            $check->check();

            return (new Status())
                ->okay()
                ->withName($check->name())
                ->withContext($check->passContext());
        } catch (HealthCheckDegradedException $exception) {
            return (new Status())
                ->degraded($exception->getMessage())
                ->withContext($exception->context())
                ->withName($exception->checkName());
        } catch (HealthCheckFailedException $exception) {
            return (new Status())
                ->problem($exception->getMessage())
                ->withContext($exception->context())
                ->withName($exception->checkName());
        }
    }
}
