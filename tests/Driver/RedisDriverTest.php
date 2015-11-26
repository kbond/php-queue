<?php

namespace Zenstruck\Queue\Tests\Driver;

use Zenstruck\Queue\Driver\RedisDriver;
use Zenstruck\Queue\Tests\DriverTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RedisDriverTest extends DriverTest
{
    const QUEUE_NAME = 'foo';

    /** @var \Redis */
    private $client;

    protected function setUp()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('The '.__CLASS__.' requires the use of redis');

            return;
        }

        $this->client = new \Redis();
        $this->client->connect('localhost');
        $this->tearDown();
    }

    protected function tearDown()
    {
        if (!extension_loaded('redis')) {
            return;
        }

        $this->client->del(self::QUEUE_NAME);
    }

    public function createDriver()
    {
        return new RedisDriver($this->client, self::QUEUE_NAME);
    }

    public function pushInvalidData()
    {
        $this->client->rPush(self::QUEUE_NAME, 'invalid data');
    }
}
