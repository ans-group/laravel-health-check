<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Middleware;

use closure;
use Illuminate\Http\Request;
use UKFast\HealthCheck\Facade\HealthCheck;

class AddHeaders
{
    public function handle(Request $request, closure $next): mixed
    {
        $response = $next($request);

        HealthCheck::all()->each(function ($check) use ($response): void {
            $header = "X-{$check->name()}-status";
            $status = $check->status()->isOkay() ? 1 : 0;

            $response->headers->set($header, (string) $status);
        });

        return $response;
    }
}
