<?php

namespace UKFast\HealthCheck\Checks;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Container\Container;
use UKFast\HealthCheck\HealthCheck;

class HttpHealthCheck extends HealthCheck
{
    protected $name = 'http';

    public function status()
    {
        $container = Container::getInstance();

        $client = $container->makeWith(Client::class, [
            'timeout' => config('healthcheck.default-curl-timeout', 2.0)
        ]);

        $badResponses = [];
        $badConnections = [];
        $generalFailures = [];
        foreach (config('healthcheck.addresses') as $address => $code) {
            if (is_int($address)) {
                $address = $code;
                $code = config('healthcheck.default-response-code', 200);
            }

            try {
                $response = $client->get($address);
            } catch (ConnectException $e) {
                $badConnections[$address] = $e->getMessage();
                continue;
            } catch (BadResponseException $e) {
                $response = $e->getResponse();
            } catch (\Exception $e) {
                $generalFailures[$address] = $this->exceptionContext($e);
                continue;
            }

            if ($response->getStatusCode() != $code) {
                $badResponses[$address] = [
                    'expected' => $code,
                    'got' => $response->getStatusCode(),
                ];
            }
        }

        if (!$badResponses && !$badConnections && !$generalFailures) {
            return $this->okay();
        }

        return $this->problem('Some HTTP connections are not working', [
            'incorrect_status_code' => $badResponses,
            'could_not_connect' => $badConnections,
            'general_failures' => $generalFailures,
        ]);
    }
}
