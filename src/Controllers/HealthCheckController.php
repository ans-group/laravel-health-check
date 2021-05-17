<?php

namespace UKFast\HealthCheck\Controllers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use UKFast\HealthCheck\Status;

class HealthCheckController
{
    public function __invoke(Container $container)
    {
        $checks = new Collection;
        foreach (config('healthcheck.checks') as $check) {
            $checks->push($container->make($check));
        }

        $statuses = $checks->map(function ($check) {
            return $check->status();
        });

        $isProblem = $statuses->contains(function ($status) {
            return $status->isProblem();
        });

        $isDegraded = $statuses->contains(function ($status) {
            return $status->isDegraded();
        });

        $body = ['status' => ($isProblem ? Status::PROBLEM : ($isDegraded ? Status::DEGRADED : Status::OKAY))];
        foreach ($statuses as $status) {
            $body[$status->name()] = [];
            $body[$status->name()]['status'] = $status->getStatus();

            if (!$status->isOkay()) {
                $body[$status->name()]['message'] = $status->message();
            }

            if (!empty($status->context())) {
                $body[$status->name()]['context'] = $status->context();
            }
        }

        return new Response($body, $isProblem ? 500 : 200);
    }
}
