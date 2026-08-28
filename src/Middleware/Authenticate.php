<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Gates the health endpoint behind HTTP basic auth, a shared header token,
 * or an IP allowlist — whichever the caller matches. Basic auth exists for
 * older monitoring systems that can't send custom headers; the header token
 * is for anything that can; the IP allowlist is for callers you'd rather
 * trust by network location than by credential. Each method only
 * authenticates if it's actually configured (see `healthcheck.auth`), so
 * enabling one doesn't implicitly open the others.
 */
class Authenticate
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if (
            $this->passesIpAllowlist($request)
            || $this->passesBasicAuth($request)
            || $this->passesHeaderAuth($request)
        ) {
            return $response;
        }

        return new Response(null, $response->status());
    }

    private function passesIpAllowlist(Request $request): bool
    {
        $allowedIps = array_map(strval(...), (array) config('healthcheck.auth.allowed-ips', []));

        if ($allowedIps === []) {
            return false;
        }

        $clientIp = $request->ip();

        if ($clientIp === null) {
            return false;
        }

        return IpUtils::checkIp($clientIp, $allowedIps);
    }

    private function passesBasicAuth(Request $request): bool
    {
        $sentUser = (string) $request->getUser();
        $sentPassword = (string) $request->getPassword();

        if ($sentUser === '' && $sentPassword === '') {
            return false;
        }

        $configuredUser = (string) config('healthcheck.auth.user', '');
        $configuredPassword = (string) config('healthcheck.auth.password', '');

        return hash_equals($configuredUser, $sentUser) && hash_equals($configuredPassword, $sentPassword);
    }

    private function passesHeaderAuth(Request $request): bool
    {
        $configuredToken = (string) config('healthcheck.auth.token', '');

        if ($configuredToken === '') {
            return false;
        }

        $headerName = (string) config('healthcheck.auth.header', 'X-Health-Check-Token');
        $sentToken = (string) $request->header($headerName, '');

        if ($sentToken === '') {
            return false;
        }

        return hash_equals($configuredToken, $sentToken);
    }
}
