<?php

namespace Zenstruck\Queue;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Adapter
{
    /**
     * Push a new message onto the queue.
     *
     * @param Message $message The job to push
     */
    public function push(Message $message);

    /**
     * Pop the next job off of the queue.
     *
     * @return Job|null
     */
    public function pop();

    /**
     * Release the job back onto the queue (increases it's attempt count).
     *
     * @param Job $job
     */
    public function release(Job $job);

    /**
     * Delete a job from the queue.
     *
     * @param Job $job
     */
    public function delete(Job $job);
}
