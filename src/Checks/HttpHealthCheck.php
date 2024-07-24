<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class HttpHealthCheck extends HealthCheck
{
    protected string $name = 'http';

    public function status(): Status
    {
        $container = Container::getInstance();

        $client = $container->makeWith(Client::class, [
            'timeout' => config('healthcheck.default-curl-timeout', 2.0)
        ]);

        /**
         * @var Collection<string, array<string, string>> $badResponses
         */
        $badResponses = collect();

        /**
         * @var Collection<string, string> $badConnections
         */
        $badConnections = collect();

        /**
         * @var Collection<string, string> $generalFailures
         */
        $generalFailures = collect();

        foreach (config('healthcheck.addresses') as $address => $code) {
            if (is_int($address)) {
                $address = $code;
                $code = config('healthcheck.default-response-code', 200);
            }

            try {
                $response = $client->get($address);
            } catch (ConnectException $exception) {
                $badConnections->put($address, $exception->getMessage());

                continue;
            } catch (BadResponseException $exception) {
                $response = $exception->getResponse();
            } catch (Exception $exception) {
                $generalFailures->put($address, $this->exceptionContext($exception));

                continue;
            }

            if ($response->getStatusCode() != $code) {
                $badResponses->put($address, [
                    'expected' => $code,
                    'got' => $response->getStatusCode(),
                ]);
            }
        }

        if ($this->isOkay($badResponses, $badConnections, $generalFailures)) {
            return $this->okay();
        }

        return $this->problem('Some HTTP connections are not working', [
            'incorrect_status_code' => $badResponses,
            'could_not_connect' => $badConnections,
            'general_failures' => $generalFailures,
        ]);
    }

    /**
     * @param Collection<string, array<string, string>> $badResponses
     * @param Collection<string, string> $badConnections
     * @param Collection<string, string> $generalFailures
     */
    private function isOkay(Collection $badResponses, Collection $badConnections, Collection $generalFailures): bool
    {
        return $badResponses->isEmpty() && $badConnections->isEmpty() && $generalFailures->isEmpty();
    }
}
