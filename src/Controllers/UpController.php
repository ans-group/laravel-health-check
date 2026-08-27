<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Controllers;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Throwable;
use UKFast\HealthCheck\Exceptions\HealthCheckFailedException;
use UKFast\HealthCheck\HealthCheckRunner;
use UKFast\HealthCheck\HealthReport;

class UpController
{
    public function __invoke(Request $request): JsonResponse|Response
    {
        try {
            $this->diagnose();
        } catch (Throwable $exception) {
            if (config('app.debug')) {
                throw $exception;
            }

            report($exception);

            return $this->present($request, $this->reportFromException($exception), problem: true);
        }

        return $this->present($request, $this->currentReport(), problem: false);
    }

    protected function diagnose(): void
    {
        if (class_exists(DiagnosingHealth::class)) {
            Event::dispatch(new DiagnosingHealth());

            return;
        }

        app(HealthCheckRunner::class)->run();
    }

    protected function currentReport(): HealthReport
    {
        if (app()->bound(HealthReport::class)) {
            return app(HealthReport::class);
        }

        return new HealthReport();
    }

    protected function reportFromException(Throwable $exception): HealthReport
    {
        if ($exception instanceof HealthCheckFailedException && $exception->report() instanceof HealthReport) {
            return $exception->report();
        }

        $report = new HealthReport();
        $report->recordDown('application', $exception->getMessage());

        return $report;
    }

    protected function present(Request $request, HealthReport $report, bool $problem): JsonResponse|Response
    {
        $status = $problem
            ? (int) config('healthcheck.default-problem-http-code', 500)
            : 200;

        $payload = $report->toArray();

        if ($request->expectsJson()) {
            return response()->json($payload, $status);
        }

        return response()->view('healthcheck::up', [
            'report' => $report,
            'payload' => $payload,
            'problem' => $problem,
        ], $status);
    }
}
