<?php

namespace UKFast\HealthCheck\Checks;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use UKFast\HealthCheck\HealthCheck;

class CrossServiceHealthCheck extends HealthCheck
{
    protected $name = 'x-service-checks';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Client $http, Request $request)
    {
        $this->http = $http;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function status()
    {
        if ($this->request->headers->has('X-Service-Check')) {
            return $this->okay('Skipped, X-Service-Check header is present');
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
