<?php

namespace UKFast\HealthCheck;

use Exception;
use Illuminate\Support\Collection;
use UKFast\HealthCheck\Exceptions\CheckNotFoundException;

class AppHealth
{
    public function __construct(
        /**
         * @var Collection<int, HealthCheck>
         */
        protected Collection $checks,
    ) {
    }

    public function passes($checkName)
    {
        $check = $this->checks->filter(fn($check): bool => $check->name() == $checkName)
            ->first();

        if (!$check) {
            throw new CheckNotFoundException($checkName);
        }

        try {
            return $check->status()->isOkay();
        } catch (Exception) {
            return false;
        }
    }

    public function fails($checkName): bool
    {
        return !$this->passes($checkName);
    }

    /**
     * Returns a collection of all health checks
     *
     * @return Collection<int, HealthCheck>
     */
    public function all(): Collection
    {
        return $this->checks;
    }
}
