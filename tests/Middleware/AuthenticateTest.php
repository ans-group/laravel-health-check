<?php

declare(strict_types=1);

namespace Tests\Middleware;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;
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
}
