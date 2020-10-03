<?php

namespace Tests\Checks;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\HttpHealthCheck;

class HttpHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    /**
     * @test
     */
    public function shows_problem_if_response_code_is_incorrect()
    {
        config([
            'healthcheck.addresses' => [
                'http://ukfast.co.uk' => 422,
            ],
            'default-response-code' => 200,
            'default-curl-timeout' => 1
        ]);

        $this->app->bind(Client::class, function ($app, $args) {
            $responses = [
                (new \GuzzleHttp\Psr7\Response(500)),
            ];
            $mockHandler = new MockHandler($responses);

            return new Client(['handler' => $mockHandler]);
        });

        $status = (new HttpHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_problem_if_connection_is_unreachable()
    {
        config([
            'healthcheck.addresses' => [
                '192.168.0.1'
            ],
            'default-response-code' => 200,
            'default-curl-timeout' => 1
        ]);

        $this->app->bind(Client::class, function ($app, $args) {
            $responses = [
                (new \GuzzleHttp\Psr7\Response(500)),
            ];
            $mockHandler = MockHandler::createWithMiddleware($responses);

            return new Client(['handler' => $mockHandler]);
        });

        $status = (new HttpHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_problem_on_connect_exception()
    {
        config([
            'healthcheck.addresses' => [
                '192.168.0.1'
            ],
            'default-response-code' => 200,
            'default-curl-timeout' => 1
        ]);

        $this->app->bind(Client::class, function ($app, $args) {
            $exceptions = [
                (new \GuzzleHttp\Exception\ConnectException('Connection refused', new \GuzzleHttp\Psr7\Request('GET', 'test'))),
            ];
            $mockHandler = new MockHandler($exceptions);

            return new Client(['handler' => $mockHandler]);
        });

        $status = (new HttpHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_problem_on_general_exception()
    {
        config([
            'healthcheck.addresses' => [
                '192.168.0.1'
            ],
            'default-response-code' => 200,
            'default-curl-timeout' => 1
        ]);

        $this->app->bind(Client::class, function ($app, $args) {
            $exceptions = [
                (new \GuzzleHttp\Exception\TooManyRedirectsException('Will not follow more than 5 redirects', new \GuzzleHttp\Psr7\Request('GET', 'test'))),
            ];
            $mockHandler = new MockHandler($exceptions);

            return new Client(['handler' => $mockHandler]);
        });

        $status = (new HttpHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    /**
     * @test
     */
    public function shows_okay_if_all_connections_are_reachable()
    {
        config([
            'healthcheck.addresses' => [
                'https://ukfast.co.uk'
            ],
            'default-response-code' => 200,
            'default-curl-timeout' => 1,
        ]);

        $this->app->bind(Client::class, function ($app, $args) {
            $responses = [
                (new \GuzzleHttp\Psr7\Response(200)),
            ];
            $mockHandler = new MockHandler($responses);

            return new Client(['handler' => $mockHandler]);
        });

        $status = (new HttpHealthCheck())->status();

        $this->assertTrue($status->isOkay());
    }
}
