<?php

namespace Zenstruck\Queue\Event;

use Zenstruck\Queue\Job;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class JobEvent extends Event
{
    private $job;

    /**
     * @param Job $job The job for this event
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * @return Job
     */
    public function getJob()
    {
        return $this->job;
    }
}
