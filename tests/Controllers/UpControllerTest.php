<?php

declare(strict_types=1);

namespace Tests\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Stubs\Checks\AlwaysDegradedCheck;
use Tests\Stubs\Checks\AlwaysDownCheck;
use Tests\Stubs\Checks\AlwaysUpCheck;
use Tests\TestCase;
use UKFast\HealthCheck\HealthCheckServiceProvider;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Event;

class UpControllerTest extends TestCase
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

    public function testReturnsOverallStatusOfUpWhenEverythingIsHealthy(): void
    {
        $this->setChecks([AlwaysUpCheck::class]);

        $this->getJson('/up')
            ->assertOk()
            ->assertExactJson([
                'status' => 'up',
                'checks' => [
                    'always-up' => ['status' => 'up'],
                ],
            ]);
    }

    public function testHealthRouteIsNotRegistered(): void
    {
        $this->setChecks([AlwaysUpCheck::class]);

        $this->get('/health')->assertNotFound();
        $this->getJson('/health')->assertNotFound();
    }

    public function testHtmlPresentationMatchesLaravelsNativeWording(): void
    {
        $this->setChecks([AlwaysUpCheck::class]);

        $this->get('/up')
            ->assertOk()
            ->assertSee('Application up')
            ->assertDontSee('Application experiencing problems', false);
    }

    public function testHtmlPresentationReusesLaravelsNativeHealthPageMarkup(): void
    {
        $this->setChecks([AlwaysUpCheck::class]);

        $this->get('/up')
            ->assertOk()
            ->assertSee('cdn.jsdelivr.net/npm/@tailwindcss/browser', false)
            ->assertSee('HTTP request received.', false);
    }

    public function testHtmlPresentationListsEachCheck(): void
    {
        $this->setChecks([AlwaysUpCheck::class, AlwaysDownCheck::class]);

        $this->get('/up')
            ->assertStatus(500)
            ->assertSee('always-up')
            ->assertSee('always-down')
            ->assertSee('Something went wrong');
    }

    public function testAPublishedViewOverridesThePackageViewWithoutAnyConfigChange(): void
    {
        $target = resource_path('views/vendor/healthcheck/up.blade.php');
        File::ensureDirectoryExists(dirname($target));
        File::put($target, 'CUSTOM VIEW: status is {{ $payload["status"] }}, problem={{ $problem ? "yes" : "no" }}');

        $this->setChecks([AlwaysUpCheck::class]);

        try {
            $this->get('/up')->assertSee('CUSTOM VIEW: status is up, problem=no', false);
        } finally {
            File::delete($target);
        }
    }

    public function testHtmlPresentationShowsAPlaceholderForAPassingCheckWithNoMessage(): void
    {
        $this->setChecks([AlwaysUpCheck::class]);

        $this->get('/up')
            ->assertOk()
            ->assertSee('—', false);
    }

    public function testOverridesDefaultUpPath(): void
    {
        app('router')->setRoutes(app(RouteCollection::class));

        $this->get('/up')->assertNotFound();

        config([
            'healthcheck.path' => '/upz',
        ]);

        /** @var HealthCheckServiceProvider $provider */
        $provider = $this->app->getProvider(HealthCheckServiceProvider::class);
        $provider->boot();

        $this->setChecks([AlwaysUpCheck::class]);

        $this->getJson('/upz')->assertOk()->assertJsonPath('status', 'up');
        $this->get('/up')->assertNotFound();
    }

    public function testReturnsDegradedCheckWithoutFailingTheEndpoint(): void
    {
        $this->setChecks([AlwaysUpCheck::class, AlwaysDegradedCheck::class]);

        $this->getJson('/up')
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                'status' => 'up',
                'checks' => [
                    'always-up' => ['status' => 'up'],
                    'always-degraded' => [
                        'status' => 'degraded',
                        'message' => 'Something went wrong',
                        'context' => ['debug' => 'info'],
                    ],
                ],
            ]);
    }

    public function testReturnsDownWhenACheckFails(): void
    {
        $this->setChecks([AlwaysUpCheck::class, AlwaysDownCheck::class]);

        $this->getJson('/up')
            ->assertStatus(500)
            ->assertJsonPath('status', 'down')
            ->assertJsonPath('checks.always-up.status', 'up')
            ->assertJsonPath('checks.always-down.status', 'down')
            ->assertJsonPath('checks.always-down.message', 'Something went wrong')
            ->assertJsonMissingPath('message');
    }

    public function testAppDiagnosingHealthListenersCanFailTheEndpoint(): void
    {
        $this->setChecks([AlwaysUpCheck::class]);

        Event::listen(DiagnosingHealth::class, function (): void {
            throw new RuntimeException('cache is down');
        });

        $this->getJson('/up')
            ->assertStatus(500)
            ->assertJsonPath('status', 'down')
            ->assertJsonPath('checks.application.message', 'cache is down')
            ->assertJsonMissingPath('message');
    }

    /**
     * @param array<int, class-string> $checks
     */
    protected function setChecks(array $checks): void
    {
        config(['healthcheck.checks' => $checks]);
    }
}
