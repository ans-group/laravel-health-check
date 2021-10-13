<?php

namespace Tests\Checks;

use Tests\TestCase;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;
use UKFast\HealthCheck\Checks\RedisHealthCheck;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Redis\Connections\PhpRedisClusterConnection;

class RedisHealthCheckTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return ['UKFast\HealthCheck\HealthCheckServiceProvider'];
    }

    /**
     * @test
     */
    public function shows_okay_if_can_ping_predis()
    {
        Config::set("database.redis.client", "predis");

        $redis = $this->getMockBuilder(RedisManager::class)
            ->setMethods(['ping'])
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->exactly(1))
            ->method('ping')
            ->willReturn(null);

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertTrue($status->isOkay());
    }

    /**
     * @test
     */
    public function shows_problem_if_cannot_ping_predis()
    {
        Config::set("database.redis.client", "predis");

        $redis = $this->getMockBuilder(RedisManager::class)
            ->setMethods(['ping'])
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
     * @test
     */
    public function shows_okay_if_cannot_ping_predis_cluster()
    {
        Config::set("database.redis.client", "predis");

        $redis = $this->getMockBuilder(RedisManager::class)
            ->setMethods(['ping'])
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->exactly(1))
            ->method('ping')
            ->willReturn(null);

        Redis::swap($redis);

        $status = (new RedisHealthCheck)->status();
        $this->assertTrue($status->isOkay());
    }

    /**
     * @test
     */
    public function shows_problem_if_cannot_ping_predis_cluster()
    {
        Config::set("database.redis.client", "predis");

        $redis = $this->getMockBuilder(RedisManager::class)
            ->setMethods(['ping'])
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
     *
     * @test
     */
    public function shows_okay_if_can_ping_phpredis()
    {
        Config::set("database.redis.client", "phpredis");

        $redisConn = $this->getMockBuilder(PhpRedisConnection::class)
            ->setMethods(['ping'])
            ->disableOriginalConstructor()
            ->getMock();

        $redisConn->expects($this->exactly(1))
            ->method('ping')
            ->willReturn(null);

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
     *
     * @test
     */
    public function shows_problem_if_cannot_ping_phpredis()
    {
        Config::set("database.redis.client", "phpredis");

        $redisConn = $this->getMockBuilder(PhpRedisConnection::class)
            ->setMethods(['ping'])
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
     *
     * @test
     */
    public function shows_okay_if_can_ping_phpredis_cluster()
    {
        Config::set("database.redis.client", "phpredis");

        $redisConn = $this->getMockBuilder(PhpRedisClusterConnection::class)
            ->setMethods(['ping', '_masters'])
            ->disableOriginalConstructor()
            ->getMock();

        $redisConn->expects($this->exactly(1))
            ->method('_masters')
            ->willReturn([
                ['master1', '6379'],
                ['master2', '6379'],
                ['master3', '6379'],
            ]);

        $redisConn->expects($this->exactly(3))
            ->method('ping')
            ->withConsecutive(
                [['master1', '6379']], 
                [['master2', '6379']], 
                [['master3', '6379']]
            )
            ->willReturn(null);

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
     *
     * @test
     */
    public function shows_problem_if_cannot_ping_first_phpredis_cluster_master()
    {
        Config::set("database.redis.client", "phpredis");

        $redisConn = $this->getMockBuilder(PhpRedisClusterConnection::class)
            ->setMethods(['ping', '_masters'])
            ->disableOriginalConstructor()
            ->getMock();

        $redisConn->expects($this->exactly(1))
            ->method('_masters')
            ->willReturn([
                ['master1', '6379'],
                ['master2', '6379'],
                ['master3', '6379'],
            ]);

        $redisConn->expects($this->exactly(1))
            ->method('ping')
            ->withConsecutive([['master1', '6379']])
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
     *
     * @test
     */
    public function shows_problem_if_cannot_ping_third_phpredis_cluster_master()
    {
        Config::set("database.redis.client", "phpredis");

        $redisConn = $this->getMockBuilder(PhpRedisClusterConnection::class)
            ->setMethods(['ping', '_masters'])
            ->disableOriginalConstructor()
            ->getMock();

        $redisConn->expects($this->exactly(1))
            ->method('_masters')
            ->willReturn([
                ['master1', '6379'],
                ['master2', '6379'],
                ['master3', '6379'],
            ]);

        $redisConn->expects($this->exactly(3))
            ->method('ping')
            ->withConsecutive(
                [['master1', '6379']], 
                [['master2', '6379']], 
                [['master3', '6379']]
            )
            ->willReturnCallback(function($node) {
                if($node[0] == 'master3') {
                    throw new \Exception("cannot connect to master3:6379");
                }

                return null;
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
