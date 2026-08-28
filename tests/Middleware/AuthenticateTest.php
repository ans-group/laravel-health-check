<?php

declare(strict_types=1);

namespace Tests\Middleware;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;
use UKFast\HealthCheck\DnsHostnameResolver;
use UKFast\HealthCheck\Middleware\Authenticate;

class AuthenticateTest extends TestCase
{
    public function testOnlyShowsStatusCodeIfFailsBasicAuth(): void
    {
        config([
            'healthcheck.auth.user' => 'correct-user',
            'healthcheck.auth.password' => 'correct-password',
        ]);

        $request = Request::create('/health', 'GET', [], [], [], [
            'PHP_AUTH_USER' => 'wrong-user',
            'PHP_AUTH_PW' => 'wrong-password',
        ]);

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
        $this->assertSame(500, $response->status());
    }

    public function testOnlyShowsStatusCodeIfNoCredentialsArePassed(): void
    {
        config([
            'healthcheck.auth.user' => 'correct-user',
            'healthcheck.auth.password' => 'correct-password',
        ]);

        $request = Request::create('/health', 'GET');

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
        $this->assertSame(500, $response->status());
    }

    public function testShowsFullResponseIfPassesBasicAuth(): void
    {
        config([
            'healthcheck.auth.user' => 'correct-user',
            'healthcheck.auth.password' => 'correct-password',
        ]);

        $request = Request::create('/health', 'GET', [], [], [], [
            'PHP_AUTH_USER' => 'correct-user',
            'PHP_AUTH_PW' => 'correct-password',
        ]);

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
        $this->assertSame(500, $response->status());
    }

    public function testShowsFullResponseIfHeaderTokenMatches(): void
    {
        config(['healthcheck.auth.token' => 'correct-token']);

        $request = Request::create('/health', 'GET');
        $request->headers->set('X-Health-Check-Token', 'correct-token');

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
    }

    public function testOnlyShowsStatusCodeIfHeaderTokenDoesNotMatch(): void
    {
        config(['healthcheck.auth.token' => 'correct-token']);

        $request = Request::create('/health', 'GET');
        $request->headers->set('X-Health-Check-Token', 'wrong-token');

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
    }

    public function testOnlyShowsStatusCodeIfNoHeaderTokenIsSent(): void
    {
        config(['healthcheck.auth.token' => 'correct-token']);

        $request = Request::create('/health', 'GET');

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
    }

    public function testHeaderNameIsConfigurable(): void
    {
        config([
            'healthcheck.auth.token' => 'correct-token',
            'healthcheck.auth.header' => 'X-Custom-Token',
        ]);

        $request = Request::create('/health', 'GET');
        $request->headers->set('X-Custom-Token', 'correct-token');

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
    }

    public function testEitherMethodPassingIsSufficient(): void
    {
        config([
            'healthcheck.auth.user' => 'correct-user',
            'healthcheck.auth.password' => 'correct-password',
            'healthcheck.auth.token' => 'correct-token',
        ]);

        $request = Request::create('/health', 'GET');
        $request->headers->set('X-Health-Check-Token', 'correct-token');

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
    }

    public function testAnUnconfiguredHeaderTokenNeverAuthenticatesEvenIfBothSidesAreEmpty(): void
    {
        $request = Request::create('/health', 'GET');
        $request->headers->set('X-Health-Check-Token', '');

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
    }

    public function testShowsFullResponseIfIpv4AddressIsAllowed(): void
    {
        config(['healthcheck.auth.allowed-ips' => ['203.0.113.7']]);

        $request = Request::create('/health', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.7']);

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
    }

    public function testShowsFullResponseIfIpv4AddressIsWithinAnAllowedCidrRange(): void
    {
        config(['healthcheck.auth.allowed-ips' => ['203.0.113.0/24']]);

        $request = Request::create('/health', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.99']);

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
    }

    public function testShowsFullResponseIfIpv6AddressIsWithinAnAllowedCidrRange(): void
    {
        config(['healthcheck.auth.allowed-ips' => ['2001:db8::/32']]);

        $request = Request::create('/health', 'GET', [], [], [], ['REMOTE_ADDR' => '2001:db8::1']);

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
    }

    public function testOnlyShowsStatusCodeIfIpAddressIsNotAllowed(): void
    {
        config(['healthcheck.auth.allowed-ips' => ['203.0.113.7']]);

        $request = Request::create('/health', 'GET', [], [], [], ['REMOTE_ADDR' => '198.51.100.1']);

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
    }

    public function testAnUnconfiguredIpAllowlistNeverAuthenticates(): void
    {
        $request = Request::create('/health', 'GET', [], [], [], ['REMOTE_ADDR' => '198.51.100.1']);

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
    }

    public function testIpAllowlistPassingIsSufficientAlongsideOtherConfiguredMethods(): void
    {
        config([
            'healthcheck.auth.user' => 'correct-user',
            'healthcheck.auth.password' => 'correct-password',
            'healthcheck.auth.token' => 'correct-token',
            'healthcheck.auth.allowed-ips' => ['203.0.113.7'],
        ]);

        $request = Request::create('/health', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.7']);

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
    }

    public function testShowsFullResponseIfClientIpMatchesAResolvedHostname(): void
    {
        config(['healthcheck.auth.allowed-hostnames' => ['my-home.example.com']]);

        $request = Request::create('/health', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.7']);

        $authenticate = new Authenticate($this->resolverFor(['203.0.113.7']));
        $response = $authenticate->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
    }

    public function testOnlyShowsStatusCodeIfClientIpDoesNotMatchAResolvedHostname(): void
    {
        config(['healthcheck.auth.allowed-hostnames' => ['my-home.example.com']]);

        $request = Request::create('/health', 'GET', [], [], [], ['REMOTE_ADDR' => '198.51.100.1']);

        $authenticate = new Authenticate($this->resolverFor(['203.0.113.7']));
        $response = $authenticate->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
    }

    public function testAnUnconfiguredHostnameAllowlistNeverAuthenticates(): void
    {
        $request = Request::create('/health', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.7']);

        $authenticate = new Authenticate($this->resolverFor(['203.0.113.7']));
        $response = $authenticate->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
    }

    public function testHostnameAllowlistPassingIsSufficientAlongsideOtherConfiguredMethods(): void
    {
        config([
            'healthcheck.auth.user' => 'correct-user',
            'healthcheck.auth.password' => 'correct-password',
            'healthcheck.auth.allowed-hostnames' => ['my-home.example.com'],
        ]);

        $request = Request::create('/health', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.7']);

        $authenticate = new Authenticate($this->resolverFor(['203.0.113.7']));
        $response = $authenticate->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
    }

    public function testShowsFullResponseWhenBypassedInLocalAndEnvironmentIsLocal(): void
    {
        config(['healthcheck.auth.bypass-in-local' => true]);
        $this->app['env'] = 'local';

        $request = Request::create('/health', 'GET');

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('body', $response->getContent());
    }

    public function testBypassInLocalHasNoEffectOutsideTheLocalEnvironment(): void
    {
        config(['healthcheck.auth.bypass-in-local' => true]);
        $this->app['env'] = 'production';

        $request = Request::create('/health', 'GET');

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
    }

    public function testBypassInLocalIsOffByDefaultEvenInTheLocalEnvironment(): void
    {
        $this->app['env'] = 'local';

        $request = Request::create('/health', 'GET');

        $response = (new Authenticate())->handle($request, fn(): ResponseFactory|Response => response('body', 500));

        $this->assertSame('', $response->getContent());
    }

    /**
     * @param array<int, string> $addresses
     */
    private function resolverFor(array $addresses): DnsHostnameResolver
    {
        return new class ($addresses) extends DnsHostnameResolver {
            public ?string $seenHostname = null;

            /**
             * @param array<int, string> $addresses
             */
            public function __construct(private readonly array $addresses)
            {
            }

            /**
             * @return array<int, string>
             */
            public function resolve(string $hostname): array
            {
                $this->seenHostname = $hostname;

                return $this->addresses;
            }
        };
    }
}
