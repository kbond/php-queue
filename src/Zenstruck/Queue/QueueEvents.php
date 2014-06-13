<?php

namespace Zenstruck\Queue;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class QueueEvents
{
    /**
     * Occurs after the message is pushed to the queue.
     */
    const POST_PUSH = 'zenstruck_queue.post_push';

    /**
     * Occurs before the job is consumed.
     */
    const PRE_CONSUME = 'zenstruck_queue.pre_consume';

    /**
     * Consumes the job.
     */
    const CONSUME = 'zenstruck_queue.consume';

    /**
     * Occurs after the job is consumed.
     *
     * This event allows you to modify the job status before it is handled.
     */
    const POST_CONSUME = 'zenstruck_queue.post_consume';
}
