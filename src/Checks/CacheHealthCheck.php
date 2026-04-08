<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use UKFast\HealthCheck\HealthCheck;
use Carbon\Carbon;
use UKFast\HealthCheck\Status;

class CacheHealthCheck extends HealthCheck
{
    protected string $name = 'cache';

    /**
     * @var array<int, string> $workingStores
     */
    protected array $workingStores = [];

    /**
     * @var array<int, array<string, string>>
     */
    protected array $incorrectValues = [];

    /**
     * @var array<int, array<string, string>> $exceptions
     */
    protected array $exceptions = [];

    public function status(): Status
    {
        foreach (config('healthcheck.cache.stores') as $store) {
            try {
                $cache = Cache::store($store);
                $key = 'laravel-health-check.' . bin2hex(random_bytes(32));

                $cache->put($key, 'healthy', Carbon::now()->addMinutes(1));

                $value = $cache->pull($key, 'broken');

                if ($value != 'healthy') {
                    $this->incorrectValues[] = [
                        'store' => $store,
                        'value' => $value
                    ];
                    continue;
                }

                $this->workingStores[] = $store;
            } catch (Exception $exception) {
                $this->exceptions[] = [
                    'store' => $store,
                    'error' => $exception->getMessage()
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
