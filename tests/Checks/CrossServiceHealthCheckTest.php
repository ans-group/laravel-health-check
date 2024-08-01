<?php

namespace Tests\Checks;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\CrossServiceHealthCheck;

class CrossServiceHealthCheckTest extends TestCase
{
    public function testReturnsOkayIfAllRelatedServicesAreUp(): void
    {
        config(['healthcheck.x-service-checks' => ['http://api.example.com/health']]);

        $container = [];
        $client = $this->mockClient(new Response(200), $container);
        $request = Request::create('/');

        $check = new CrossServiceHealthCheck($client, $request);

        $this->assertTrue($check->status()->isOkay());
        $this->assertCount(1, $container);
        $this->assertSame('http://api.example.com/health', (string) $container[0]['request']->getUri());
        $this->assertTrue(isset($container[0]['request']->getHeaders()['X-Service-Check']));
    }

    public function testReturnsProblemIfAtLeastOneServiceIsDown(): void
    {
        config(['healthcheck.x-service-checks' => ['http://api.example.com/health']]);

        $container = [];
        $client = $this->mockClient(new Response(500), $container);
        $request = Request::create('/');

        $check = new CrossServiceHealthCheck($client, $request);

        $this->assertTrue($check->status()->isProblem());
        $this->assertCount(1, $container);
        $this->assertSame('http://api.example.com/health', (string) $container[0]['request']->getUri());
        $this->assertTrue(isset($container[0]['request']->getHeaders()['X-Service-Check']));
    }

    public function testSkipsCheckIfXServiceCheckHeaderIsPresent(): void
    {
        config(['healthcheck.x-service-checks' => ['http://api.example.com/health']]);

        $container = [];
        $client = $this->mockClient(new Response(500), $container);
        $request = Request::create('/');
        $request->headers->set('X-Service-Check', 'true');

        $check = new CrossServiceHealthCheck($client, $request);

        $this->assertTrue($check->status()->isOkay());
        $this->assertSame(
            [
                'message' => 'Skipped, X-Service-Check header is present'
            ],
            $check->status()->context(),
        );
        $this->assertCount(0, $container);
    }

    private function mockClient(Response $responses, array &$container): Client
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler(Arr::wrap($responses));

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        return new Client(['handler' => $handlerStack]);
    }
}
