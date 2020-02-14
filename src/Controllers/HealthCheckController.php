<?php

namespace UKFast\HealthCheck\Controllers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

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

        $isOkay = $statuses->filter(function ($status) {
            return $status->isProblem();
        })->isEmpty();

        $body = ['status' => $isOkay ? 'OK' : 'PROBLEM'];
        foreach ($statuses as $status) {
            $body[$status->name()] = [];
            $body[$status->name()]['status'] = $status->isOkay() ? 'OK' : 'PROBLEM';

            if ($status->isProblem()) {
                $body[$status->name()]['message'] = $status->message();
            }

            if (!empty($status->context())) {
                $body[$status->name()]['context'] = $status->context();
            }
        }

        return new Response($body, $isOkay ? 200 : 500);
    }
}
