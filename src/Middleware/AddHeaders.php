<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Middleware;

use closure;
use Illuminate\Http\Request;
use Throwable;
use UKFast\HealthCheck\Facade\HealthCheck;
use UKFast\HealthCheck\HealthCheck as Check;
use UKFast\HealthCheck\HealthReport;

/**
 * Adds an `X-{check}-status` header per response. When applied to the health
 * endpoint's own route, this reuses the `HealthReport` already produced by
 * that request rather than re-running checks. Applied to any other route it
 * has no report to reuse and runs the check suite itself — apply it only to
 * the health endpoint's route to avoid that cost.
 */
class AddHeaders
{
    public function handle(Request $request, closure $next): mixed
    {
        $response = $next($request);

        $report = app()->bound(HealthReport::class) ? app(HealthReport::class) : null;

        HealthCheck::all()->each(function (Check $check) use ($response, $report): void {
            $header = "X-{$check->name()}-status";
            $status = $report?->statusFor($check->name());
            $ok = $status !== null ? $status !== 'down' : $this->runCheck($check);

            $response->headers->set($header, $ok ? '1' : '0');
        });

        return $response;
    }

    private function runCheck(Check $check): bool
    {
        try {
            $check->check();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
