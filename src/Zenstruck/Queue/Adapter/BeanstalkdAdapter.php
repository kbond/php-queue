<?php

namespace Zenstruck\Queue\Adapter;

use Zenstruck\Queue\Adapter;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Message;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class BeanstalkdAdapter implements Adapter
{
    private $client;
    private $tube;

    public function __construct(\Pheanstalk_Pheanstalk $client, $tube)
    {
        $this->client = $client;
        $this->tube = $tube;
    }

    /**
     * {@inheritdoc}
     */
    public function push(Message $message)
    {
        $this->client->useTube($this->tube);

        $this->client->put(
            serialize($message),
            \Pheanstalk_Pheanstalk::DEFAULT_PRIORITY,
            (int) $message->getDelay()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $rawJob = $this->client->watchOnly($this->tube)->reserve(0);

        if (!$rawJob instanceof \Pheanstalk_Job) {
            return null;
        }

        $message = @unserialize($rawJob->getData());

        if (!$message instanceof Message) {
            // can't handle, requeue
            $this->client->release($rawJob, 2048);
            return null;
        }

        $attempts = isset($this->client->statsJob($rawJob)['reserves']) ?
            (int) $this->client->statsJob($rawJob)['reserves'] : 1
        ;

        return new Job($rawJob, $message, $attempts);
    }

    /**
     * {@inheritdoc}
     */
    public function release(Job $job)
    {
        $this->client->release(
            $job->getId(),
            2048,
            (int) $job->createRequeueMessage()->getDelay()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Job $job)
    {
        $this->client->delete($job->getId());
    }
}
