<?php

declare(strict_types=1);

namespace Tests\Middleware;

use Illuminate\Http\Request;
use Tests\Stubs\Checks\AlwaysDownCheck;
use Tests\Stubs\Checks\AlwaysUpCheck;
use Tests\TestCase;
use UKFast\HealthCheck\AppHealth;
use UKFast\HealthCheck\HealthReport;
use UKFast\HealthCheck\Middleware\AddHeaders;

class AddHeadersTest extends TestCase
{
    public function testAddsHeadersForEachCheck(): void
    {
        $this->app->bind('app-health', fn(): AppHealth => new AppHealth(collect([
            new AlwaysUpCheck(),
            new AlwaysDownCheck(),
        ])));

        $request = Request::create('/health', 'GET');
        $response = (new AddHeaders())->handle($request, fn($request) => response()->json(null, 500));

        $this->assertSame('1', $response->headers->get('X-always-up-status'));
        $this->assertSame('0', $response->headers->get('X-always-down-status'));
    }

    public function testReusesTheAlreadyComputedReportInsteadOfRerunningChecks(): void
    {
        $ranTwice = new class extends AlwaysUpCheck {
            public int $runs = 0;

            public function check(): void
            {
                $this->runs++;

                parent::check();
            }
        };

        $this->app->bind('app-health', fn(): AppHealth => new AppHealth(collect([$ranTwice])));

        $report = new HealthReport();
        $report->recordDown($ranTwice->name());
        $this->app->instance(HealthReport::class, $report);

        $request = Request::create('/health', 'GET');
        $response = (new AddHeaders())->handle($request, fn($request) => response()->json(null, 500));

        $this->assertSame('0', $response->headers->get("X-{$ranTwice->name()}-status"));
        $this->assertSame(0, $ranTwice->runs);
    }

    public function testTreatsADegradedCheckFromAReusedReportAsNotOk(): void
    {
        $check = new AlwaysUpCheck();

        $this->app->bind('app-health', fn(): AppHealth => new AppHealth(collect([$check])));

        $report = new HealthReport();
        $report->recordDegraded($check->name(), 'Something is degraded');
        $this->app->instance(HealthReport::class, $report);

        $request = Request::create('/health', 'GET');
        $response = (new AddHeaders())->handle($request, fn($request) => response()->json(null, 200));

        $this->assertSame('0', $response->headers->get("X-{$check->name()}-status"));
    }
}
