<?php

namespace Tests\Middleware;

use Illuminate\Http\Request;
use Tests\TestCase;
use UKFast\HealthCheck\Middleware\BasicAuth;

class BasicAuthTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config([
            'healthcheck.auth.user' => 'correct-user',
            'healthcheck.auth.password' => 'correct-password'
        ]);
    }

    /**
     * @test
     */
    public function only_shows_status_code_if_fails_basic_auth()
    {
        $request = Request::create('/health', 'GET', [], [], [], [
            'PHP_AUTH_USER' => 'wrong-user',
            'PHP_AUTH_PW' => 'wrong-password',
        ]);

        $response = (new BasicAuth)->handle($request, function () {
            return response('body', 500);
        });

        $this->assertEquals('', $response->getContent());
        $this->assertEquals(500, $response->status());
    }

    /**
     * @test
     */
    public function only_shows_status_code_if_no_auth_credentials_are_passed()
    {
        $request = Request::create('/health', 'GET');

        $response = (new BasicAuth)->handle($request, function () {
            return response('body', 500);
        });

        $this->assertEquals('', $response->getContent());
        $this->assertEquals(500, $response->status());
    }

    /**
     * @test
     */
    public function shows_full_response_if_passes_basic_auth()
    {
        $request = Request::create('/health', 'GET', [], [], [], [
            'PHP_AUTH_USER' => 'correct-user',
            'PHP_AUTH_PW' => 'correct-password',
        ]);

        $response = (new BasicAuth)->handle($request, function () {
            return response('body', 500);
        });

        $this->assertEquals('body', $response->getContent());
        $this->assertEquals(500, $response->status());
    }
}
