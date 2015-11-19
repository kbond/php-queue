<?php

namespace Zenstruck\Queue;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Driver
{
    /**
     * @param Payload $payload
     */
    public function push(Payload $payload);

    /**
     * @return Job|null
     */
    public function pop();

    /**
     * @param Job $job
     */
    public function release(Job $job);

    /**
     * @param Job $job
     */
    public function delete(Job $job);
}
