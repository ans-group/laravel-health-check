<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Checks;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use UKFast\HealthCheck\HealthCheck;

class CrossServiceHealthCheck extends HealthCheck
{
    protected string $name = 'x-service-checks';

    public function __construct(
        protected Client $http,
        protected Request $request
    ) {
    }

    public function check(): void
    {
        if ($this->request->headers->has('X-Service-Check')) {
            $this->pass([
                'message' => 'Skipped, X-Service-Check header is present',
            ]);

            return;
        }

        /**
         * @var array<int, array<string, string|array<string, string>>> $failedServices
         */
        $failedServices = [];
        foreach (config('healthcheck.x-service-checks') as $service) {
            try {
                $this->http->get($service, [
                    'headers' => ['X-Service-Check' => true],
                ]);
            } catch (GuzzleException $exception) {
                $failedServices[] = [
                    'service' => $service,
                    'exception' => $this->exceptionContext($exception),
                ];
            }
        }

        if ($failedServices !== []) {
            $this->fail("Some services failed", $failedServices);
        }
    }
}
