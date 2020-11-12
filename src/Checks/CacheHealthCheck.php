<?php

namespace UKFast\HealthCheck\Checks;

use Illuminate\Support\Facades\Cache;
use UKFast\HealthCheck\HealthCheck;

class CacheHealthCheck extends HealthCheck
{
    const NAME = 'cache';

    protected $name = self::NAME;

    protected $workingStores = [];
    
    protected $incorrectValues = [];

    protected $exceptions = [];

    public function status()
    {
        foreach (config('healthcheck.cache.stores') as $store) {
            try {
                $cache = Cache::store($store);

                $cache->put('laravel-health-check', 'healthy', 60);
                
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
