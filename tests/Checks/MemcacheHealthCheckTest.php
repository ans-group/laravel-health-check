<?php

namespace Tests\Checks;

use Illuminate\Cache\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UKFast\HealthCheck\Checks\MemcacheHealthCheck;

class MemcacheHealthCheckTest extends TestCase
{
    /**
     * @var MemcacheHealthCheck|MockObject
     */
    protected $healthCheck;

    /**
     * @var Repository|MockObject
     */
    protected $memCacheMock;

    public function prepare()
    {
        $this->healthCheck = $this->getMockBuilder(MemcacheHealthCheck::class)
            ->setMethods(['getMemCache', 'getTime'])->getMock();

        $this->memCacheMock = $this->getMockBuilder(Repository::class)
            ->setMethods(['set','get'])->disableOriginalConstructor()->getMock();

        $this->healthCheck->expects($this->any())->method('getMemCache')->willReturn($this->memCacheMock);
    }

    /**
     * Can return false when memcached is not reachable
     *
     * @return void
     */
    public function can_return_false_when_memcache_is_not_available()
    {
        $this->prepare();
        $this->memCacheMock->expects($this->once())->method('get')->willReturn(false);
        $this->assertFalse($this->healthCheck->status()->isOkay());
    }

    /**
     * Can return false when memcached is throwing an exception
     *
     * @return void
     */
    public function can_return_false_when_memcache_is_throwing_InvalidArgumentException()
    {
        $this->prepare();
        $fakedException = new \Exception('Some exception during memcache write');
        $this->memCacheMock->expects($this->once())->method('set')->willThrowException($fakedException);
        $this->assertFalse($this->healthCheck->status()->isOkay());
    }

    /**
     * @return void
     */
    public function can_return_true_when_memcache_is_running()
    {
        $this->prepare();
        $this->mockWorkingMemcache();
        $this->assertTrue($this->healthCheck->status()->isOkay());
    }

    /**
     * @return void
     */
    protected function mockWorkingMemcache()
    {
        $fakeTime = 4711;
        $this->healthCheck->expects($this->any())->method('getTime')->willReturn($fakeTime);
        $this->memCacheMock->expects($this->any())->method('get')->willReturn($fakeTime);
    }
}
