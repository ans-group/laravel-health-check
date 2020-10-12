<?php

namespace UKFast\HealthCheck\Checks;

use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class MemcacheHealthCheck extends HealthCheck
{
    protected $name = 'memcache';

    /**
     * @var CacheInterface
     */
    protected $memcacheStore;

    /**
     * @return Status
     */
    public function status()
    {
        try {
            if (!$this->isMemCacheHealthy()) {
                return $this->problem('Could not write and read to memcache', []);
            }
        } catch (\Exception $e) {
            return $this->problem('Exceptions during memcache check', [
                'exception' => $this->exceptionContext($e),
            ]);
        }

        return $this->okay();
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function isMemCacheHealthy()
    {
        $checktime = $this->getTime();
        $this->getMemCache()->set('heathcheck_time', $checktime);
        $cacheResult = $this->getMemCache()->get('heathcheck_time');

        return $cacheResult === $checktime;
    }

    /**
     * @return CacheInterface
     */
    protected function getMemCache()
    {
        if (is_null($this->memcacheStore)) {
            $this->memcacheStore =  Cache::store('memcached');
        }
        return $this->memcacheStore;
    }

    /**
     * @return int
     */
    protected function getTime()
    {
        return time();
    }
}
