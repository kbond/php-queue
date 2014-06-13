<?php

namespace Zenstruck\Queue;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Job
{
    const STATUS_UNKNOWN = 0;
    const STATUS_REQUEUE = 1;
    const STATUS_FAILED  = 2;
    const STATUS_DELETE  = 3;

    private $id;
    private $message;
    private $attempts;
    private $status = self::STATUS_UNKNOWN;
    private $requeueDelay = null;
    private $failMessage = null;

    /**
     * @param mixed   $id       The job identifier
     * @param Message $message  The message for this job
     * @param int     $attempts The number of times this job was attempted
     */
    public function __construct($id, Message $message, $attempts)
    {
        $this->id = $id;
        $this->message = $message;
        $this->attempts = $attempts;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->message->getData();
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        return $this->message->getInfo();
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->message->getMetadata();
    }

    /**
     * @return Message
     */
    public function createRequeueMessage()
    {
        return new Message($this->getData(), $this->getInfo(), $this->getMetadata(), $this->requeueDelay);
    }

    /**
     * @return null|string
     */
    public function getFailMessage()
    {
        return $this->failMessage;
    }

    /**
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Mark the job as failed.
     *
     * @param string   $message Info about the failure
     * @param int|null $delay   The requeue delay
     */
    public function fail($message, $delay = null)
    {
        $this->failMessage = $message;
        $this->requeueDelay = $delay;
        $this->status = self::STATUS_FAILED;
    }

    /**
     * Mark the job as requeue.
     *
     * @param int|null $delay The requeue delay
     */
    public function requeue($delay = null)
    {
        $this->requeueDelay = $delay;
        $this->status = self::STATUS_REQUEUE;
    }

    /**
     * Mark the job for deletion.
     */
    public function delete()
    {
        $this->status = self::STATUS_DELETE;
    }
}
