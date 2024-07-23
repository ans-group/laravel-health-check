<?php

namespace Tests\Middleware;

use Illuminate\Http\Request;
use Tests\Stubs\Checks\AlwaysDownCheck;
use Tests\Stubs\Checks\AlwaysUpCheck;
use Tests\TestCase;
use UKFast\HealthCheck\AppHealth;
use UKFast\HealthCheck\Middleware\AddHeaders;

class AddHeadersTest extends TestCase
{
    public function testAddsHeadersForEachCheck(): void
    {
        $this->app->bind('app-health', function () {
            return new AppHealth(collect([
                new AlwaysUpCheck(),
                new AlwaysDownCheck(),
            ]));
        });

        $request = Request::create('/health', 'GET');
        $response = (new AddHeaders())->handle($request, function ($request) {
            return response()->json(null, 500);
        });

        $this->assertSame('1', $response->headers->get('X-always-up-status'));
        $this->assertSame('0', $response->headers->get('X-always-down-status'));
    }
}
