<?php

namespace UKFast\HealthCheck;

use Illuminate\Support\Collection;

class AppHealth
{
    /**
     * @var Collection $checks
     */
    protected $checks;

    public function __construct($checks)
    {
        $this->checks = $checks;
    }

    public function passes($checkName)
    {
        $check = $this->checks->filter(function ($check) use ($checkName) {
            return $check->name() == $checkName;
        })->first();

        try {
            return $check ? $check->status()->isOkay() : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function fails($checkName)
    {
        return !$this->passes($checkName);
    }

    public function all()
    {
        return $this->checks->count();
    }
}
