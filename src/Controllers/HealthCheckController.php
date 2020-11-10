<?php

namespace UKFast\HealthCheck\Controllers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Response;
use UKFast\HealthCheck\HealthCheck;

class HealthCheckController
{
    public function __invoke(Container $container)
    {
        $body = [];
        $isOkay = true;
        /** @var HealthCheck $check */
        foreach (\UKFast\HealthCheck\Facade\HealthCheck::all() as $check) {
            $status = $check->status();

            if ($isOkay && !$status->isOkay()) {
                $isOkay = false;
            }

            $body[$status->name()] = [];
            $body[$status->name()]['status'] = $status->isOkay() ? 'OK' : 'PROBLEM';

            if ($status->isProblem()) {
                $body[$status->name()]['message'] = $status->message();
            }

            if (!empty($status->context())) {
                $body[$status->name()]['context'] = $status->context();
            }
        }

        $body = array_merge(['status' => $isOkay ? 'OK' : 'PROBLEM'], $body);

        return new Response($body, $isOkay ? 200 : 500);
    }
}
