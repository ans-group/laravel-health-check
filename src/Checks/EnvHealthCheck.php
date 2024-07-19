<?php

namespace UKFast\HealthCheck\Checks;

use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class EnvHealthCheck extends HealthCheck
{
    protected string $name = 'env';

    public function status(): Status
    {
        $default = config('healthcheck.env-check-key', 'HEALTH_CHECK_ENV_DEFAULT_VALUE');

        foreach (config('healthcheck.required-env') as $env) {
            if (env($env, $default) === $default) {
                $missing[] = $env;
            }
        }

        if (empty($missing)) {
            return $this->okay();
        }

        return $this->problem('Missing env params', [
            'missing' => $missing,
        ]);
    }
}
