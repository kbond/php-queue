<?php

namespace Zenstruck\Queue\Tests\Driver;

use Predis\Client;
use Zenstruck\Queue\Driver\PredisDriver;
use Zenstruck\Queue\Tests\DriverTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class PredisDriverTest extends DriverTest
{
    const QUEUE_NAME = 'foo';

    /** @var Client */
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

    public function createDriver()
    {
        return new PredisDriver($this->client, self::QUEUE_NAME);
    }

    public function pushInvalidData()
    {
        $this->client->rpush(self::QUEUE_NAME, 'invalid data');
    }
}
