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
    /**
     * @test
     */
    public function returns_okay_if_all_related_services_are_up()
    {
        config(['healthcheck.x-service-checks' => ['http://api.example.com/health']]);

        $container = [];
        $client = $this->mockClient(new Response(200), $container);
        $request = Request::create('/');

        $check = new CrossServiceHealthCheck($client, $request);

        $this->assertTrue($check->status()->isOkay());
        $this->assertSame(1, count($container));
        $this->assertSame('http://api.example.com/health', (string) $container[0]['request']->getUri());
        $this->assertTrue(isset($container[0]['request']->getHeaders()['X-Service-Check']));
    }

    /**
     * @test
     */
    public function returns_problem_if_at_least_one_service_is_down()
    {
        config(['healthcheck.x-service-checks' => ['http://api.example.com/health']]);

        $container = [];
        $client = $this->mockClient(new Response(500), $container);
        $request = Request::create('/');

        $check = new CrossServiceHealthCheck($client, $request);

        $this->assertTrue($check->status()->isProblem());
        $this->assertSame(1, count($container));
        $this->assertSame('http://api.example.com/health', (string) $container[0]['request']->getUri());
        $this->assertTrue(isset($container[0]['request']->getHeaders()['X-Service-Check']));
    }

    /**
     * @test
     */
    public function skips_check_if_x_service_check_header_is_present()
    {
        config(['healthcheck.x-service-checks' => ['http://api.example.com/health']]);

        $container = [];
        $client = $this->mockClient(new Response(500), $container);
        $request = Request::create('/');
        $request->headers->set('X-Service-Check', true);

        $check = new CrossServiceHealthCheck($client, $request);

        $this->assertTrue($check->status()->isOkay());
        $this->assertSame('Skipped, X-Service-Check header is present', $check->status()->context());
        $this->assertSame(0, count($container));
    }

    private function mockClient($responses, &$container)
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler(Arr::wrap($responses));

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        return new Client(['handler' => $handlerStack]);
    }
}
