<?php

namespace Tests\Checks;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Tests\Stubs\Redis\Connections\PhpRedisClusterConnection;
use Tests\Stubs\Redis\Connections\PhpRedisConnection;
use Tests\Stubs\Redis\RedisManager;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\RedisHealthCheck;
use UKFast\HealthCheck\HealthCheckServiceProvider;

class RedisHealthCheckTest extends TestCase
{
    /**
     * @param Application $app
     * @return array<int, class-string>
     */
    public function getPackageProviders($app): array

    {
        return [HealthCheckServiceProvider::class];
    }

    public function testShowsOkayIfCanPingPredis(): void
    {
        Config::set("database.redis.client", "predis");

        $redis = $this->getMockBuilder(RedisManager::class)
            ->onlyMethods(['ping'])
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->exactly(1))
            ->method('ping')
            ->willReturn('pong');

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertTrue($status->isOkay());
    }

    public function testShowsProblemIfCannotPingPredis(): void
    {
        Config::set("database.redis.client", "predis");

        $redis = $this->getMockBuilder(RedisManager::class)
            ->onlyMethods(['ping'])
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->exactly(1))
            ->method('ping')
            ->willThrowException(new \Exception("cannot ping"));

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertFalse($status->isOkay());
    }

    public function testShowsOkayIfCannotPingPredisCluster(): void
    {
        Config::set("database.redis.client", "predis");

        $redis = $this->getMockBuilder(RedisManager::class)
            ->onlyMethods(['ping'])
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->exactly(1))
            ->method('ping')
            ->willReturn(false);

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertTrue($status->isOkay());
    }

    public function testShowsProblemIfCannotPingPredisCluster(): void
    {
        Config::set("database.redis.client", "predis");

        $redis = $this->getMockBuilder(RedisManager::class)
            ->onlyMethods(['ping'])
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->exactly(1))
            ->method('ping')
            ->willThrowException(new \Exception("cannot ping"));

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertFalse($status->isOkay());
    }

    /**
     * Scenario: phpredis connection for single instance is healthy
     */
    public function testShowsOkayIfCanPingPhpredis(): void
    {
        Config::set("database.redis.client", "phpredis");

        $redisConn = $this->getMockBuilder(PhpRedisConnection::class)
            ->onlyMethods(['ping'])
            ->disableOriginalConstructor()
            ->getMock();

        $redisConn->expects($this->exactly(1))
            ->method('ping')
            ->willReturn('pong');

        $redis = $this->createMock(RedisManager::class);

        $redis->expects($this->exactly(1))
            ->method('connection')
            ->willReturn($redisConn);

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertTrue($status->isOkay());
    }

    /**
     * Scenerio: phpredis connection for single instance is not healthy
     */
    public function testShowsProblemIfCannotPingPhpredis(): void
    {
        Config::set("database.redis.client", "phpredis");

        $redisConn = $this->getMockBuilder(PhpRedisConnection::class)
            ->onlyMethods(['ping'])
            ->disableOriginalConstructor()
            ->getMock();

        $redisConn->expects($this->exactly(1))
            ->method('ping')
            ->willThrowException(new \Exception("cannot ping"));

        $redis = $this->createMock(RedisManager::class);

        $redis->expects($this->exactly(1))
            ->method('connection')
            ->willReturn($redisConn);

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertFalse($status->isOkay());
    }

    /**
     * Scenario: phpredis connection to cluster with 3 masters that are all healthy
     */
    public function testShowsOkayIfCanPingPhpredisCluster(): void
    {
        Config::set("database.redis.client", "phpredis");

        $redisConn = $this->getMockBuilder(PhpRedisClusterConnection::class)
            ->onlyMethods(['ping', '_masters'])
            ->disableOriginalConstructor()
            ->getMock();

        $redisConn->expects($this->exactly(1))
            ->method('_masters')
            ->willReturn([
                ['master1', '6379'],
                ['master2', '6379'],
                ['master3', '6379'],
            ]);

        $matcher = $this->exactly(3);
        $redisConn->expects($matcher)
            ->method('ping')
            ->willReturnCallback(function () use ($matcher) {
                return match ($matcher->numberOfInvocations()) {
                    1 => [['master1', '6379']],
                    2 => [['master2', '6379']],
                    3 => [['master3', '6379']],
                };
            })
            ->willReturn('pong');

        $redis = $this->createMock(RedisManager::class);

        $redis->expects($this->exactly(1))
            ->method('connection')
            ->willReturn($redisConn);

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertTrue($status->isOkay());
    }

    /**
     * Scenario: phpredis connection to cluster with 3 masters; first is unhealthy
     */
    public function testShowsProblemIfCannotPingFirstPhpredisClusterMaster(): void
    {
        Config::set("database.redis.client", "phpredis");

        $redisConn = $this->getMockBuilder(PhpRedisClusterConnection::class)
            ->onlyMethods(['ping', '_masters'])
            ->disableOriginalConstructor()
            ->getMock();

        $redisConn->expects($this->exactly(1))
            ->method('_masters')
            ->willReturn([
                ['master1', '6379'],
                ['master2', '6379'],
                ['master3', '6379'],
            ]);

        $matcher = $this->exactly(1);
        $redisConn->expects($matcher)
            ->method('ping')
            ->willReturnCallback(function() use ($matcher) {
                return match ($matcher->numberOfInvocations()) {
                    1 => [['master1', '6379']],
                };
            })
            ->willThrowException(new \Exception("cannot connect to master1:6379"));

        $redis = $this->createMock(RedisManager::class);

        $redis->expects($this->exactly(1))
            ->method('connection')
            ->willReturn($redisConn);

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertFalse($status->isOkay());
    }

    /**
     * Scenario: phpredis connection to cluster with 3 masters; last is unhealthy
     */

    public function testShowsProblemIfCannotPingThirdPhpredisClusterMaster(): void
    {
        Config::set("database.redis.client", "phpredis");

        $redisConn = $this->getMockBuilder(PhpRedisClusterConnection::class)
            ->onlyMethods(['ping', '_masters'])
            ->disableOriginalConstructor()
            ->getMock();

        $redisConn->expects($this->exactly(1))
            ->method('_masters')
            ->willReturn([
                ['master1', '6379'],
                ['master2', '6379'],
                ['master3', '6379'],
            ]);

        $matcher = $this->exactly(3);
        $redisConn->expects($matcher)
            ->method('ping')
            ->willReturnCallback(function () use ($matcher) {
                return match ($matcher->numberOfInvocations()) {
                    1 => 'pong',
                    2 => 'pong',
                    3 => throw new \Exception("cannot connect to master3:6379"),
                };
            });

        $redis = $this->createMock(RedisManager::class);

        $redis->expects($this->exactly(1))
            ->method('connection')
            ->willReturn($redisConn);

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertFalse($status->isOkay());
    }
}
