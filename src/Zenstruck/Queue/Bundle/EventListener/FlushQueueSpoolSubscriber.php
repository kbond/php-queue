<?php

namespace Zenstruck\Queue\Bundle\EventListener;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zenstruck\Queue\QueueSpool;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FlushQueueSpoolSubscriber implements EventSubscriberInterface
{
    private $queue;

    /**
     * @param QueueSpool $queue
     */
    public function __construct(QueueSpool $queue)
    {
        $this->queue = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::TERMINATE => 'onKernelTerminate',
            ConsoleEvents::TERMINATE => 'onConsoleTerminate'
        );
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        $this->flushSpool();
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        $this->flushSpool();
    }

    private function flushSpool()
    {
        $this->queue->flush();
    }
}
