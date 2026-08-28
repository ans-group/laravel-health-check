<?php

declare(strict_types=1);

namespace UKFast\HealthCheck;

use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Resolves a hostname to its current IP(s), caching the result for the
 * DNS record's own TTL. On failure (no records, or the lookup throws),
 * the empty result is cached for FAILURE_CACHE_SECONDS so a DNS outage
 * doesn't force every request to pay a lookup - only the first one.
 */
class DnsHostnameResolver
{
    private const FAILURE_CACHE_SECONDS = 30;

    /**
     * @return array<int, string>
     */
    public function resolve(string $hostname): array
    {
        $cacheKey = "healthcheck:auth:allowed-hostname:{$hostname}";
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        [$addresses, $ttl] = $this->lookup($hostname);

        Cache::put($cacheKey, $addresses, max(1, $ttl));

        return $addresses;
    }

    /**
     * @return array{0: array<int, string>, 1: int}
     */
    private function lookup(string $hostname): array
    {
        try {
            $records = $this->lookupRecords($hostname);
        } catch (Throwable) {
            return [[], self::FAILURE_CACHE_SECONDS];
        }

        if ($records === false || $records === []) {
            return [[], self::FAILURE_CACHE_SECONDS];
        }

        $addresses = [];
        $ttls = [];

        foreach ($records as $record) {
            $addresses[] = $record['ip'] ?? $record['ipv6'] ?? null;
            $ttls[] = $record['ttl'] ?? self::FAILURE_CACHE_SECONDS;
        }

        return [array_values(array_filter($addresses)), min($ttls)];
    }

    /**
     * @return array<int, array<string, mixed>>|false
     */
    protected function lookupRecords(string $hostname): array|false
    {
        return dns_get_record($hostname, DNS_A | DNS_AAAA);
    }
}
