<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Checks;

use UKFast\HealthCheck\HealthCheck;

class EnvHealthCheck extends HealthCheck
{
    protected string $name = 'env';

    public function check(): void
    {
        $default = config('healthcheck.env-check-key', 'HEALTH_CHECK_ENV_DEFAULT_VALUE');

        $missing = [];
        foreach (config('healthcheck.required-env') as $env) {
            if (env($env, $default) === $default) {
                $missing[] = $env;
            }
        }

        if ($missing !== []) {
            $this->fail('Missing env params', [
                'missing' => $missing,
            ]);
        }
    }
}
