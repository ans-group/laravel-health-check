<?php

namespace UKFast\HealthCheck\Checks;

use UKFast\HealthCheck\HealthCheck;

class EnvHealthCheck extends HealthCheck
{
    protected $name = 'env';
    
    public function status()
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
