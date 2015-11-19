<?php

namespace Zenstruck\Queue\Event;

use SimpleBus\Message\Name\NamedMessage;
use Zenstruck\Queue\Job;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MaxAttemptsReached implements NamedMessage
{
    private $job;
    private $maxAttempts;

    /**
     * @param Job $job
     * @param int $maxAttempts
     */
    public function __construct(Job $job, $maxAttempts)
    {
        if (!$job->isFailed()) {
            throw new \LogicException('Job is not marked as failed.');
        }

        $this->job = $job;
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * @return Job
     */
    public function job()
    {
        return $this->job;
    }

    /**
     * @return int
     */
    public function maxAttempts()
    {
        return $this->maxAttempts;
    }

    /**
     * @return \Exception
     */
    public function exception()
    {
        return $this->job->failedException();
    }

    /**
     * {@inheritdoc}
     */
    public static function messageName()
    {
        return 'queue.max_attempts_reached';
    }
}
