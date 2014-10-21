<?php

namespace Zenstruck\Queue\Tests\Functional;

use Predis\Client;
use Zenstruck\Queue\Adapter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class PredisAdapterTest extends AdapterTest
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
        $this->client->del(self::QUEUE_NAME.':delayed');
    }

    /**
     * {@inheritdoc}
     */
    protected function createAdapter()
    {
        return new Adapter\PredisAdapter($this->client, self::QUEUE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function pushInvalidData()
    {
        $this->client->rpush(self::QUEUE_NAME, 'invalid data');
    }

}
