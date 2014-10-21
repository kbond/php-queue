<?php

namespace Zenstruck\Queue\Adapter;

use Zenstruck\Queue\Adapter;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Message;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class BaseRedisAdapter implements Adapter
{
    private $queueName;

    public function __construct($queueName)
    {
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

        $rawPayload = $this->lPop($this->queueName);

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
            $this->zAdd($this->getDelayedQueueName(), time() + $delay, $payload);

            return;
        }

        $this->rPush($this->queueName, $payload);
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
     * @param string $queueName
     *
     * @return string|null
     */
    abstract protected function lPop($queueName);

    /**
     * @param string $queueName
     * @param int    $delay
     * @param string $payload
     */
    abstract protected function zAdd($queueName, $delay, $payload);

    /**
     * @param string $queueName
     * @param string $payload
     */
    abstract protected function rPush($queueName, $payload);

    /**
     * @param string     $queueName
     * @param string|int $start
     * @param string|int $end
     *
     * @return array
     */
    abstract protected function zRangeByScore($queueName, $start, $end);

    /**
     * @param string     $queueName
     * @param string|int $start
     * @param string|int $end
     */
    abstract protected function zRemRangeByScore($queueName, $start, $end);

    /**
     * @return string
     */
    private function getDelayedQueueName()
    {
        return sprintf('%s:delayed', $this->queueName);
    }

    /**
     * Migrates delayed jobs to the main queue
     */
    private function migrateExpiredJobs()
    {
        $time = time();
        $queueName = $this->getDelayedQueueName();

        // get expired jobs
        $jobs = $this->zRangeByScore($queueName, '-inf', $time);

        if (0 === count($jobs)) {
            return;
        }

        // remove expired jobs
        $this->zRemRangeByScore($queueName, '-inf', $time);

        foreach ($jobs as $job) {
            $this->pushRaw($job);
        }
    }
}
