<?php

namespace Zenstruck\Queue\Tests\Functional;

use Zenstruck\Queue\Adapter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RedisAdapterTest extends BaseFunctionalTest
{
    const QUEUE_NAME = 'foo';

    private $client;

    protected function setUp()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of redis');

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
        $this->client->del(self::QUEUE_NAME.':delayed');
    }

    /**
     * {@inheritdoc}
     */
    protected function createAdapter()
    {
        return new Adapter\RedisAdapter($this->client, self::QUEUE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function pushInvalidData()
    {
        $this->client->rPush(self::QUEUE_NAME, 'invalid data');
    }
}
