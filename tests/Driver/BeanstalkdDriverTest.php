<?php

namespace Zenstruck\Queue\Tests\Driver;

use Pheanstalk\Pheanstalk;
use Zenstruck\Queue\Driver\BeanstalkdDriver;
use Zenstruck\Queue\Tests\DriverTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class BeanstalkdDriverTest extends DriverTest
{
    const TUBE = 'foo';

    /**
     * @var Pheanstalk
     */
    private $client;

    protected function setUp()
    {
        $this->client = new Pheanstalk('localhost');
        $this->tearDown();
    }

    protected function tearDown()
    {
        try {
            while ($job = $this->client->peekReady(self::TUBE)) {
                $this->client->delete($job);
            }
        } catch (\Exception $e) {
            // noop
        }

        try {
            while ($job = $this->client->peekDelayed(self::TUBE)) {
                $this->client->delete($job);
            }
        } catch (\Exception $e) {
            // noop
        }

        try {
            while ($job = $this->client->peekBuried(self::TUBE)) {
                $this->client->delete($job);
            }
        } catch (\Exception $e) {
            // noop
        }
    }

    public function createDriver()
    {
        return new BeanstalkdDriver($this->client, self::TUBE);
    }

    public function pushInvalidData()
    {
        $this->client->useTube(self::TUBE);
        $this->client->put('invalid data');
    }
}
