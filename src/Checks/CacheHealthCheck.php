<?php

namespace UKFast\HealthCheck\Checks;

use Illuminate\Support\Facades\Cache;
use UKFast\HealthCheck\HealthCheck;
use Carbon\Carbon;

class CacheHealthCheck extends HealthCheck
{
    protected $name = 'cache';

    protected $workingStores = [];
    
    protected $incorrectValues = [];

    protected $exceptions = [];

    public function status()
    {
        foreach (config('healthcheck.cache.stores') as $store) {
            try {
                $cache = Cache::store($store);

                $cache->put('laravel-health-check', 'healthy', Carbon::now()->addMinutes(1));
                
                $value = $cache->pull('laravel-health-check', 'broken');

                if ($value != 'healthy') {
                    $this->incorrectValues[] = [
                        'store' => $store,
                        'value' => $value
                    ];
                    continue;
                }

                $this->workingStores[] = $store;
            } catch (\Exception $e) {
                $this->exceptions[] = [
                    'store' => $store,
                    'error' => $e->getMessage()
                ];
            }
        }

        if (empty($this->incorrectValues) && empty($this->exceptions)) {
            return $this->okay();
        }

        return $this->problem(
            'Some cache connections are not working',
            [
                'working' => $this->workingStores,
                'incorrect_values' => $this->incorrectValues,
                'exceptions' => $this->exceptions
            ]
        );
    }
}
