<?php

namespace Zenstruck\Queue;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Listener
{
    private $queue;

    /**
     * @param Queue $queue
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Consumes jobs until an exit requirement is met.
     *
     * @param int|null $maxJobs     The number of jobs to consume before exiting
     * @param int|null $timeout     The number of seconds to consume jobs before exiting
     * @param int|null $memoryLimit The memory limit in MB - will exit if exceeded
     *
     * @return string The reason for exiting
     */
    public function listen($maxJobs = null, $timeout = null, $memoryLimit = null)
    {
        $timeout = null === $timeout ? null : time() + $timeout;
        $memoryLimit = null === $memoryLimit ? null : $memoryLimit * 1024 * 1024;
        $numJobs = 0;

        while (true) {
            if (true === $this->queue->consume()) {
                $numJobs++;
            }

            if (null !== $maxJobs && $numJobs >= $maxJobs) {
                return 'Max jobs consumed.';
            }

            if (null !== $timeout && time() >= $timeout) {
                return 'Timeout reached.';
            }

            if (null !== $memoryLimit && memory_get_usage() >= $memoryLimit) {
                return 'Memory limit reached.';
            }
        }
    }
}
