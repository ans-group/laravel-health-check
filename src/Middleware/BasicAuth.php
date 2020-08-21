<?php

namespace UKFast\HealthCheck\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BasicAuth
{
    public function handle(Request $request, $next)
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
