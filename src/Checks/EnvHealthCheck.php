<?php

namespace UKFast\HealthCheck\Checks;

use UKFast\HealthCheck\HealthCheck;

class EnvHealthCheck extends HealthCheck
{
    const NAME = 'env';

    protected $name = self::NAME;
    
    public function status()
    {
        foreach (config('healthcheck.required-env') as $env) {
            if (env($env) === null) {
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
