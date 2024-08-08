<?php

namespace Tests\Checks;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Application;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\HttpHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class HttpHealthCheckTest extends TestCase
{
    /**
     * @inheritDoc
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array
    {
        return [HealthCheckServiceProvider::class];
    }

    public function testShowsProblemIfResponseCodeIsIncorrect(): void
    {
        config([
            'healthcheck.addresses' => [
                'http://ukfast.co.uk' => 422,
            ],
            'default-response-code' => 200,
            'default-curl-timeout' => 1
        ]);

        $this->app->bind(Client::class, function (): Client {
            $responses = [
                (new Response(500)),
            ];
            $mockHandler = new MockHandler($responses);

            return new Client(['handler' => $mockHandler]);
        });

        $status = (new HttpHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsProblemIfConnectionIsUnreachable(): void
    {
        config([
            'healthcheck.addresses' => [
                '192.168.0.1'
            ],
            'default-response-code' => 200,
            'default-curl-timeout' => 1
        ]);

        $this->app->bind(Client::class, function (): Client {
            $responses = [
                (new Response(500)),
            ];
            $mockHandler = MockHandler::createWithMiddleware($responses);

            return new Client(['handler' => $mockHandler]);
        });

        $status = (new HttpHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsProblemOnConnectException(): void
    {
        config([
            'healthcheck.addresses' => [
                '192.168.0.1'
            ],
            'default-response-code' => 200,
            'default-curl-timeout' => 1
        ]);

        $this->app->bind(Client::class, function (): Client {
            $exceptions = [
                (new ConnectException('Connection refused', new Request('GET', 'test'))),
            ];
            $mockHandler = new MockHandler($exceptions);

            return new Client(['handler' => $mockHandler]);
        });

        $status = (new HttpHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsProblemOnGeneralException(): void
    {
        config([
            'healthcheck.addresses' => [
                '192.168.0.1'
            ],
            'default-response-code' => 200,
            'default-curl-timeout' => 1
        ]);

        $this->app->bind(Client::class, function (): Client {
            $exceptions = [
                (new TooManyRedirectsException('Will not follow more than 5 redirects', new Request('GET', 'test'))),
            ];
            $mockHandler = new MockHandler($exceptions);

            return new Client(['handler' => $mockHandler]);
        });

        $status = (new HttpHealthCheck())->status();

        $this->assertTrue($status->isProblem());
    }

    public function testShowsOkayIfAllConnectionsAreReachable(): void
    {
        config([
            'healthcheck.addresses' => [
                'https://ukfast.co.uk'
            ],
            'default-response-code' => 200,
            'default-curl-timeout' => 1,
        ]);

        $this->app->bind(Client::class, function (): Client {
            $responses = [
                (new Response(200)),
            ];
            $mockHandler = new MockHandler($responses);

            return new Client(['handler' => $mockHandler]);
        });

        $status = (new HttpHealthCheck())->status();

        $this->assertTrue($status->isOkay());
    }
}
