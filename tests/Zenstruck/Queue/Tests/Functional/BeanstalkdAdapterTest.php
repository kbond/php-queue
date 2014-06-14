<?php

namespace Zenstruck\Queue\Tests\Functional;

use Zenstruck\Queue\Adapter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class BeanstalkdAdapterTest extends BaseFunctionalTest
{
    const TUBE = 'foo';

    /**
     * @var \Pheanstalk_Pheanstalk
     */
    private $client;

    protected function setUp()
    {
        $this->client = new \Pheanstalk_Pheanstalk('localhost');

        $this->tearDown();
    }

    protected function tearDown()
    {
        try {
            while ($job = $this->client->peekReady(self::TUBE)) {
                $this->client->delete($job);
            }
        } catch (\Pheanstalk_Exception_ServerException $e) {
            // noop
        }

        try {
            while ($job = $this->client->peekDelayed(self::TUBE)) {
                $this->client->delete($job);
            }
        } catch (\Pheanstalk_Exception_ServerException $e) {
            // noop
        }

        try {
            while ($job = $this->client->peekBuried(self::TUBE)) {
                $this->client->delete($job);
            }
        } catch (\Pheanstalk_Exception_ServerException $e) {
            // noop
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createAdapter()
    {
        return new Adapter\BeanstalkdAdapter($this->client, self::TUBE);
    }

    /**
     * Pushes Invalid data to the queue.
     */
    protected function pushInvalidData()
    {
        $this->client->useTube(self::TUBE);

        $this->client->put('invalid data');
    }
}
