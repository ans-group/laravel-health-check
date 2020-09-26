<?php

namespace UKFast\HealthCheck\Middleware;

use Illuminate\Http\Request;
use UKFast\HealthCheck\Facade\HealthCheck;

class AddHeaders
{
    public function handle(Request $request, $next)
    {
        $response = $next($request);

        HealthCheck::all()->each(function ($check) use ($response) {
            $header = "X-{$check->name()}-status";
            $status = $check->status()->isOkay() ? 1 : 0;

            $response->headers->set($header, $status);
        });
        
        return $response;
    }
}
