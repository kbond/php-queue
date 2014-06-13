<?php

namespace Zenstruck\Queue;

use Zenstruck\Queue\Event\JobEvent;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Consumer
{
    /**
     * @param JobEvent $event
     */
    public function consume(JobEvent $event);
}
