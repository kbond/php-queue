<?php

namespace Zenstruck\Queue\Tests\Functional;

use Predis\Client;
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
        $this->client = new Client();

        $this->tearDown();
    }

    protected function tearDown()
    {
        $this->client->del(self::QUEUE_NAME);
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
        $this->client->rpush(self::QUEUE_NAME, 'invalid data');
    }

}
