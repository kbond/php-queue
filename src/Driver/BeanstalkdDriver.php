<?php

namespace Zenstruck\Queue\Driver;

use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\PheanstalkInterface;
use Zenstruck\Queue\Driver;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Payload;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class BeanstalkdDriver implements Driver
{
    private $client;
    private $tube;

    /**
     * @param PheanstalkInterface $client
     * @param string              $tube
     */
    public function __construct(PheanstalkInterface $client, $tube)
    {
        $this->client = $client;
        $this->tube = $tube;
    }

    /**
     * {@inheritdoc}
     */
    public function push(Payload $payload)
    {
        $this->client->useTube($this->tube);
        $this->client->put(json_encode($payload));
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $rawJob = $this->client->watchOnly($this->tube)->reserve(0);

        if (!$rawJob instanceof PheanstalkJob) {
            return null;
        }

        if (null === $payload = Payload::fromJson($rawJob->getData())) {
            // can't handle - requeue
            $this->client->release($rawJob, 2048);

            return null;
        }

        $stats = $this->client->statsJob($rawJob);
        $attempts = isset($stats['reserves']) ? (int) $stats['reserves'] : 1;

        return new Job($payload, $attempts, $rawJob);
    }

    /**
     * {@inheritdoc}
     */
    public function release(Job $job)
    {
        $this->client->release($job->id());
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Job $job)
    {
        $this->client->delete($job->id());
    }
}
