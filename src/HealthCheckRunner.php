<?php

declare(strict_types=1);

namespace UKFast\HealthCheck;

use Throwable;
use UKFast\HealthCheck\Exceptions\HealthCheckDegradedException;
use UKFast\HealthCheck\Exceptions\HealthCheckFailedException;

class HealthCheckRunner
{
    public function run(): HealthReport
    {
        $report = new HealthReport();

        foreach ((array) config('healthcheck.checks', []) as $classPath) {
            /** @var HealthCheck $check */
            $check = app($classPath);

            try {
                $check->check();
                $report->recordUp($check->name(), $check->passContext());
            } catch (HealthCheckDegradedException $exception) {
                $report->recordDegraded(
                    $exception->checkName(),
                    $exception->getMessage(),
                    $exception->context(),
                );
            } catch (HealthCheckFailedException $exception) {
                $report->recordDown(
                    $exception->checkName(),
                    $exception->getMessage(),
                    $exception->context(),
                );
            } catch (Throwable $exception) {
                try {
                    report($exception);
                } catch (Throwable) {
                    // Reporting itself failed — don't let that mask the
                    // original check failure.
                }

                $report->recordDown($check->name(), $exception->getMessage(), [
                    'error' => $exception->getMessage(),
                    'class' => $exception::class,
                ]);
            }
        }

        app()->instance(HealthReport::class, $report);

        if ($report->isDown()) {
            throw new HealthCheckFailedException(
                'application',
                $report->summary(),
                [],
                $report,
            );
        }

        return $report;
    }
}
