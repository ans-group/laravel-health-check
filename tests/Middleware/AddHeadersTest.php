<?php

namespace Tests\Middleware;

use Illuminate\Http\Request;
use Tests\TestCase;
use UKFast\HealthCheck\AppHealth;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Middleware\AddHeaders;

class AddHeadersTest extends TestCase
{
    /**
     * @test
     */
    public function adds_headers_for_each_check()
    {
        $this->app->bind('app-health', function () {
            return new AppHealth(collect([
                new AlwaysUpCheck,
                new AlwaysDownCheck,
            ]));
        });

        $request = Request::create('/health', 'GET');
        $response = (new AddHeaders)->handle($request, function ($request) {
            return response()->json(null, 404);
        });

        $this->assertEquals(1, $response->headers->get('X-always-up-status'));
        $this->assertEquals(0, $response->headers->get('X-always-down-status'));
    }
}

class AlwaysUpCheck extends HealthCheck
{
    protected $name = 'always-up';

    public function status()
    {
        return $this->okay();
    }
}

class AlwaysDownCheck extends HealthCheck
{
    protected $name = 'always-down';

    public function status()
    {
        return $this->problem('Something went wrong', [
            'debug' => 'info',
        ]);
    }
}