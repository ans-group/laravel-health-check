<?php

namespace UKFast\HealthCheck;

use Exception;
use Illuminate\Support\Collection;
use UKFast\HealthCheck\Exceptions\CheckNotFoundException;

class AppHealth
{

    public function __construct(
        /**
         * @var Collection<int, class-string>
         */
        protected Collection $checks,
    ) {
    }

    public function passes($checkName)
    {
        /**
         * @var HealthCheck $check
         */
        $check = $this->checks->filter(function ($check) use ($checkName) {
            return $check->name() == $checkName;
        })->first();

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
     * @return Collection<int, class-string>
     */
    public function all(): Collection
    {
        return $this->checks;
    }
}
