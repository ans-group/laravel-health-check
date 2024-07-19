<?php

namespace Tests\Middleware;

use Illuminate\Http\Request;
use Tests\TestCase;
use UKFast\HealthCheck\Middleware\BasicAuth;

class BasicAuthTest extends TestCase
{
    public function testOnlyShowsStatusCodeIfFailsBasicAuth(): void
    {
        config([
            'healthcheck.auth.user' => 'correct-user',
            'healthcheck.auth.password' => 'correct-password'
        ]);

        $request = Request::create('/health', 'GET', [], [], [], [
            'PHP_AUTH_USER' => 'wrong-user',
            'PHP_AUTH_PW' => 'wrong-password',
        ]);

        $response = (new BasicAuth)->handle($request, function () {
            return response('body', 500);
        });

        $this->assertSame('', $response->getContent());
        $this->assertSame(500, $response->status());
    }

    public function testOnlyShowsStatusCodeIfNoAuthCredentialsArePassed(): void
    {
        config([
            'healthcheck.auth.user' => 'correct-user',
            'healthcheck.auth.password' => 'correct-password'
        ]);

        $request = Request::create('/health', 'GET');

        $response = (new BasicAuth)->handle($request, function () {
            return response('body', 500);
        });

        $this->assertSame('', $response->getContent());
        $this->assertSame(500, $response->status());
    }

    public function testShowsFullResponseIfPassesBasicAuth(): void
    {
        config([
            'healthcheck.auth.user' => 'correct-user',
            'healthcheck.auth.password' => 'correct-password'
        ]);

        $request = Request::create('/health', 'GET', [], [], [], [
            'PHP_AUTH_USER' => 'correct-user',
            'PHP_AUTH_PW' => 'correct-password',
        ]);

        $response = (new BasicAuth)->handle($request, function () {
            return response('body', 500);
        });

        $this->assertSame('body', $response->getContent());
        $this->assertSame(500, $response->status());
    }
}
