<?php

namespace UKFast\HealthCheck\Checks;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class CrossServiceHealthCheck extends HealthCheck
{
    protected string $name = 'x-service-checks';

    protected Client $http;

    protected Request $request;

    public function __construct(Client $http, Request $request)
    {
        $this->http = $http;
        $this->request = $request;
    }

    public function status(): Status
    {
        if ($this->request->headers->has('X-Service-Check')) {
            return $this->okay([
                'message' => 'Skipped, X-Service-Check header is present',
            ]);
        }

        $failedServices = [];
        foreach (config('healthcheck.x-service-checks') as $service) {
            try {
                $this->http->get($service, [
                    'headers' => ['X-Service-Check' => true],
                ]);
            } catch (GuzzleException $e) {
                $failedServices[] = [
                    'service' => $service,
                    'exception' => $this->exceptionContext($e),
                ];
            }
        }

        if ($failedServices) {
            return $this->problem("Some services failed", $failedServices);
        }

        return $this->okay();
    }
}
