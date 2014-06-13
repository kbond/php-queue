<?php

namespace Zenstruck\Queue\Adapter;

use Zenstruck\Queue\Adapter;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Message;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SynchronousAdapter implements Adapter
{
    private $message = null;

    /**
     * {@inheritdoc}
     */
    public function push(Message $message)
    {
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        if (!$this->message instanceof Message) {
            return null;
        }

        $job = new Job('synchronous-job', $this->message, 1);

        $this->delete($job);

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function release(Job $job)
    {
        if (Job::STATUS_FAILED === $job->getStatus()) {
            throw new \RuntimeException(sprintf('Job "%s" failed with message "%s".', $job->getInfo(), $job->getFailMessage()));
        }

        throw new \RuntimeException(sprintf('Cannot release job "%s" with SynchronousAdapter.', $job->getInfo()));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Job $job)
    {
        $this->message = null;
    }
}
