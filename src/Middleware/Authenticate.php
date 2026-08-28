<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\IpUtils;
use UKFast\HealthCheck\DnsHostnameResolver;

/**
 * Gates the health endpoint behind HTTP basic auth, a shared header token,
 * a static IP allowlist, or a DDNS hostname allowlist — whichever the
 * caller matches. Basic auth exists for older monitoring systems that
 * can't send custom headers; the header token is for anything that can;
 * the IP allowlist is for callers you'd rather trust by network location
 * than by credential; the hostname allowlist is the same idea for callers
 * on a dynamic IP (e.g. a developer on a residential ISP behind a DDNS
 * record). Each method only authenticates if it's actually configured
 * (see `healthcheck.auth`), so enabling one doesn't implicitly open the
 * others. `healthcheck.auth.bypass-in-local` is a separate, explicit
 * opt-in to skip the gate entirely in the local environment — off by
 * default, and gated on `app()->environment('local')` rather than the
 * request's IP, since a client IP can be spoofed or misreported by a
 * misconfigured proxy but the app's own environment can't.
 */
class Authenticate
{
    public function __construct(
        private readonly DnsHostnameResolver $hostnameResolver = new DnsHostnameResolver(),
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if (
            $this->bypassedInLocal()
            || $this->passesIpAllowlist($request)
            || $this->passesHostnameAllowlist($request)
            || $this->passesBasicAuth($request)
            || $this->passesHeaderAuth($request)
        ) {
            return $response;
        }

        return new Response(null, $response->status());
    }

    private function bypassedInLocal(): bool
    {
        if (! config('healthcheck.auth.bypass-in-local', false)) {
            return false;
        }

        return app()->environment('local');
    }

    private function passesIpAllowlist(Request $request): bool
    {
        $allowedIps = array_map(strval(...), (array) config('healthcheck.auth.allowed-ips', []));

        return $this->matchesAnyIp($request, $allowedIps);
    }

    private function passesHostnameAllowlist(Request $request): bool
    {
        $hostnames = array_map(strval(...), (array) config('healthcheck.auth.allowed-hostnames', []));

        if ($hostnames === []) {
            return false;
        }

        $resolvedIps = [];

        foreach ($hostnames as $hostname) {
            array_push($resolvedIps, ...$this->hostnameResolver->resolve($hostname));
        }

        return $this->matchesAnyIp($request, $resolvedIps);
    }

    /**
     * @param array<int, string> $ips
     */
    private function matchesAnyIp(Request $request, array $ips): bool
    {
        if ($ips === []) {
            return false;
        }

        $clientIp = $request->ip();

        if ($clientIp === null) {
            return false;
        }

        return IpUtils::checkIp($clientIp, $ips);
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
