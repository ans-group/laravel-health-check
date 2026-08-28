<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use Illuminate\Support\Facades\Cache;
use UKFast\HealthCheck\DnsHostnameResolver;

class DnsHostnameResolverTest extends TestCase
{
    public function testResolvesAddressesFromDnsRecords(): void
    {
        $resolver = $this->resolverReturning([
            ['ip' => '203.0.113.7', 'ttl' => 60],
        ]);

        $this->assertSame(['203.0.113.7'], $resolver->resolve('example.test'));
    }

    public function testResolvesMultipleAddressesAcrossIpv4AndIpv6(): void
    {
        $resolver = $this->resolverReturning([
            ['ip' => '203.0.113.7', 'ttl' => 60],
            ['ipv6' => '2001:db8::1', 'ttl' => 60],
        ]);

        $this->assertSame(['203.0.113.7', '2001:db8::1'], $resolver->resolve('example.test'));
    }

    public function testCachesTheResultSoASecondResolveDoesNotLookUpAgain(): void
    {
        $resolver = new class extends DnsHostnameResolver {
            public int $lookupCalls = 0;

            /**
             * @return array<int, array<string, mixed>>
             */
            protected function lookupRecords(string $hostname): array
            {
                $this->lookupCalls++;

                return [['ip' => '203.0.113.7', 'ttl' => 60, 'host' => $hostname]];
            }
        };

        $resolver->resolve('example.test');
        $resolver->resolve('example.test');

        $this->assertSame(1, $resolver->lookupCalls);
    }

    public function testCachesTheResultForTheRecordsOwnTtl(): void
    {
        Cache::shouldReceive('get')->once()->andReturn(null);
        Cache::shouldReceive('put')->once()->with(
            'healthcheck:auth:allowed-hostname:example.test',
            ['203.0.113.7'],
            42,
        );

        $resolver = $this->resolverReturning([
            ['ip' => '203.0.113.7', 'ttl' => 42],
        ]);

        $resolver->resolve('example.test');
    }

    public function testUsesTheLowestTtlWhenMultipleRecordsDisagree(): void
    {
        Cache::shouldReceive('get')->once()->andReturn(null);
        Cache::shouldReceive('put')->once()->with(
            'healthcheck:auth:allowed-hostname:example.test',
            ['203.0.113.7', '203.0.113.8'],
            10,
        );

        $resolver = $this->resolverReturning([
            ['ip' => '203.0.113.7', 'ttl' => 60],
            ['ip' => '203.0.113.8', 'ttl' => 10],
        ]);

        $resolver->resolve('example.test');
    }

    public function testReturnsAnEmptyArrayWhenNoRecordsAreFound(): void
    {
        $resolver = $this->resolverReturning([]);

        $this->assertSame([], $resolver->resolve('example.test'));
    }

    public function testReturnsAnEmptyArrayWhenTheLookupFails(): void
    {
        $resolver = $this->resolverReturning(false);

        $this->assertSame([], $resolver->resolve('example.test'));
    }

    public function testReturnsAnEmptyArrayWhenTheLookupThrows(): void
    {
        $resolver = $this->resolverThrowing(new Exception('DNS server unreachable'));

        $this->assertSame([], $resolver->resolve('example.test'));
    }

    public function testCachesAFailedLookupBriefly(): void
    {
        Cache::shouldReceive('get')->once()->andReturn(null);
        Cache::shouldReceive('put')->once()->with(
            'healthcheck:auth:allowed-hostname:example.test',
            [],
            30,
        );

        $resolver = $this->resolverReturning([]);

        $resolver->resolve('example.test');
    }

    /**
     * @param array<int, array<string, mixed>>|false $records
     */
    private function resolverReturning(array|false $records): DnsHostnameResolver
    {
        return new class ($records) extends DnsHostnameResolver {
            public ?string $seenHostname = null;

            /**
             * @param array<int, array<string, mixed>>|false $records
             */
            public function __construct(private readonly array|false $records)
            {
            }

            /**
             * @return array<int, array<string, mixed>>|false
             */
            protected function lookupRecords(string $hostname): array|false
            {
                $this->seenHostname = $hostname;

                return $this->records;
            }
        };
    }

    private function resolverThrowing(Exception $exception): DnsHostnameResolver
    {
        return new class ($exception) extends DnsHostnameResolver {
            public function __construct(private readonly Exception $exception)
            {
            }

            /**
             * @return array<int, array<string, mixed>>|false
             */
            protected function lookupRecords(string $hostname): array|false
            {
                throw new Exception("{$this->exception->getMessage()} (looking up {$hostname})");
            }
        };
    }
}
