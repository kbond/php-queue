<?php

namespace Zenstruck\Queue;

use Zenstruck\Queue\Event\JobEvent;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class BaseConsumer implements Consumer
{
    /**
     * {@inheritdoc}
     */
    final public function consume(JobEvent $event)
    {
        if ($this->supports($event)) {
            $this->doConsume($event);
        }
    }

    /**
     * Whether this consumer supports this job event.
     *
     * @param JobEvent $event
     *
     * @return bool
     */
    abstract protected function supports(JobEvent $event);

    /**
     * Consume this job event.
     *
     * @param JobEvent $event
     */
    abstract protected function doConsume(JobEvent $event);
}
