<?php

namespace Zenstruck\Queue\Adapter;

use Predis\Client;
use Zenstruck\Queue\Adapter;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Message;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RedisAdapter implements Adapter
{
    private $client;
    private $queueName;

    public function __construct(Client $client, $queueName)
    {
        $this->client = $client;
        $this->queueName = $queueName;
    }

    /**
     * {@inheritdoc}
     */
    public function push(Message $message)
    {
        $this->pushRaw($this->encodePayload($message), $message->getDelay());
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $this->migrateExpiredJobs();

        $rawPayload = $this->client->lpop($this->queueName);

        if (null === $rawPayload) {
            // empty queue
            return null;
        }

        $payload = @json_decode($rawPayload, true);

        if (!$payload) {
            // can't handle, requeue
            $this->pushRaw($rawPayload);

            return null;
        }

        $message = @unserialize($payload['message']);

        if (!$message) {
            // can't handle, requeue
            $this->pushRaw($rawPayload);

            return null;
        }

        return new Job('redis-job', $message, $payload['attempts'] + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function release(Job $job)
    {
        $message = $job->createRequeueMessage();

        $this->pushRaw($this->encodePayload($message, $job->getAttempts()), $message->getDelay());
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Job $job)
    {
        // Job is deleted during pop
    }

    /**
     * @param string   $payload
     * @param int|null $delay
     */
    private function pushRaw($payload, $delay = null)
    {
        if ($delay) {
            $this->client->zadd($this->getDelayedQueueName(), time() + $delay, $payload);

            return;
        }

        $this->client->rpush($this->queueName, $payload);
    }

    /**
     * @param Message $message
     * @param int     $attempts
     *
     * @return string
     */
    private function encodePayload(Message $message, $attempts = 0)
    {
        return json_encode(
            array(
                'message' => serialize($message),
                'attempts' => $attempts
            )
        );
    }

    /**
     * Migrates delayed jobs to the main queue
     */
    private function migrateExpiredJobs()
    {
        $time = time();
        $queueName = $this->getDelayedQueueName();

        // get expired jobs
        $jobs = $this->client->zrangebyscore($queueName, '-inf', $time);

        if (0 === count($jobs)) {
            return;
        }

        // remove expired jobs
        $this->client->zremrangebyscore($queueName, '-inf', $time);

        foreach ($jobs as $job) {
            $this->pushRaw($job);
        }
    }

    /**
     * @return string
     */
    private function getDelayedQueueName()
    {
        return sprintf('%s:delayed', $this->queueName);
    }
}
