<?php

namespace UKFast\HealthCheck\Middleware;

use closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BasicAuth
{
    public function handle(Request $request, closure $next): mixed
    {
        $isAuthenticated = $request->getUser() == config('healthcheck.auth.user')
            && $request->getPassword() == config('healthcheck.auth.password');

        $sentCredentials = $request->getUser() != '' || $request->getPassword() != '';

        $response = $next($request);

        if ($isAuthenticated && $sentCredentials) {
            return $response;
        }

        return new Response(null, $response->status());
    }
}
