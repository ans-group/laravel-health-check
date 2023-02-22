<?php

namespace UKFast\HealthCheck\Controllers;

use Illuminate\Support\Arr;
use UKFast\HealthCheck\Status;
use Illuminate\Contracts\Container\Container;

class HealthCheckController
{
    public function __invoke(Container $container)
    {
        Arr::set($body, 'status', Status::OKAY);

        $hasProblem = false;

        foreach (config('healthcheck.checks') as $check) {
            $status = $container->make($check)->status();

            Arr::set($body, $status->name() . '.status', $status->getStatus());
            if (!$status->isOkay()) {
                Arr::set($body, $status->name() . '.message', $status->message());
            }

            if (!empty($status->context())) {
                Arr::set($body, $status->name() . '.context', $status->context());
            }

            if ($status->getStatus() == Status::PROBLEM && $hasProblem == false) {
                $hasProblem = true;
                Arr::set($body, 'status', Status::PROBLEM);
            }

            if ($status->getStatus() == Status::DEGRADED && $hasProblem == false) {
                Arr::set($body, 'status', Status::DEGRADED);
            }
        }

        return response()
            ->json($body, in_array(Arr::get($body, 'status'), [Status::DEGRADED, Status::OKAY])  ? 200 : 500);
    }
}
