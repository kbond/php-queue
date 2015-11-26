<?php

namespace Zenstruck\Queue\Driver;

use Zenstruck\Queue\Driver;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Payload;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class BaseRedisDriver implements Driver
{
    private $queueName;

    /**
     * @param string $queueName
     */
    public function __construct($queueName)
    {
        $this->queueName = $queueName;
    }

    /**
     * {@inheritdoc}
     */
    public function push(Payload $payload)
    {
        $this->rPush($this->queueName, $this->encodePayload($payload));
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $rawPayload = $this->lPop($this->queueName);

        if (null === $rawPayload) {
            // empty queue
            return null;
        }

        if (null === $job = $this->decodePayload($rawPayload)) {
            // can't handle, requeue
            $this->rPush($this->queueName, $rawPayload);

            return null;
        }

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function release(Job $job)
    {
        $this->rPush($this->queueName, $this->encodePayload($job->payload(), $job->attempts()));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Job $job)
    {
        // noop - job is deleted during pop
    }

    /**
     * @param Payload $payload
     * @param int     $attempts
     *
     * @return string
     */
    private function encodePayload(Payload $payload, $attempts = 0)
    {
        return json_encode([
            'payload' => $payload,
            'attempts' => $attempts,
        ]);
    }

    /**
     * @param string $rawPayload
     *
     * @return Job|null
     */
    private function decodePayload($rawPayload)
    {
        $decodedPayload = json_decode($rawPayload, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return null;
        }

        if (!is_array($decodedPayload)) {
            return null;
        }

        if (!isset($decodedPayload['payload']) || !isset($decodedPayload['attempts'])) {
            return null;
        }

        if (!is_array($decodedPayload['payload'])) {
            return null;
        }

        if (null === $payload = Payload::fromArray($decodedPayload['payload'])) {
            return null;
        }

        return new Job($payload, $decodedPayload['attempts'] + 1);
    }

    /**
     * @param string $queueName
     * @param string $payload
     */
    abstract protected function rPush($queueName, $payload);

    /**
     * @param string $queueName
     *
     * @return string|null
     */
    abstract protected function lPop($queueName);
}
