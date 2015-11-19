<?php

namespace Zenstruck\Queue\Event;

use SimpleBus\Message\Name\NamedMessage;
use Zenstruck\Queue\Job;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FailedJob implements NamedMessage
{
    private $job;

    public function __construct(Job $job)
    {
        if (!$job->isFailed()) {
            throw new \LogicException('Job is not marked as failed.');
        }

        $this->job = $job;
    }

    /**
     * @return Job
     */
    public function job()
    {
        return $this->job;
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
        return 'queue.failed_job';
    }
}
