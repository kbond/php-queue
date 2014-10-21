<?php

namespace Zenstruck\Queue;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zenstruck\Queue\Adapter\SynchronousAdapter;
use Zenstruck\Queue\Event\Event;
use Zenstruck\Queue\Event\JobEvent;
use Zenstruck\Queue\Event\MessageEvent;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Queue implements Pushable
{
    private $adapter;
    private $dispatcher;
    private $failUnknown;

    /**
     * @param Adapter                  $adapter     The queue adapter
     * @param EventDispatcherInterface $dispatcher  The event dispatcher
     * @param bool                     $failUnknown Whether to fail jobs whose status hasn't been implicitly set:
     *                                              - true: jobs with status unknown are assumed to have not been consumed (fail)
     *                                              - false: jobs with status unknown are assumed to have been consumed (delete)
     */
    public function __construct(Adapter $adapter, EventDispatcherInterface $dispatcher, $failUnknown = false)
    {
        $this->adapter = $adapter;
        $this->dispatcher = $dispatcher;
        $this->failUnknown = $failUnknown;
    }

    /**
     * Add a consumer to the event dispatcher.
     *
     * @param Consumer $consumer
     * @param int      $priority
     */
    public function addConsumer(Consumer $consumer, $priority = 0)
    {
        $this->dispatcher->addListener(QueueEvents::CONSUME, array($consumer, 'consume'), $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function push($data, $info, array $metadata = array(), $delay = null)
    {
        $this->doPush(new Message($data, $info, $metadata, $delay));
    }

    /**
     * Consumes the next job in the queue.
     *
     * @return bool Whether or not a job was consumed
     */
    public function consume()
    {
        $job = $this->adapter->pop();

        if (!$job instanceof Job) {
            return false;
        }

        $this->dispatchEvent(QueueEvents::PRE_CONSUME, new JobEvent($job));

        try {
            $this->dispatchEvent(QueueEvents::CONSUME, new JobEvent($job));
        } catch (\Exception $e) {
            $job->fail($e->getMessage());
        }

        if ($this->failUnknown && Job::STATUS_UNKNOWN === $job->getStatus()) {
            $job->fail('No consumer set or consumer failed to set status.');
        }

        $this->dispatchEvent(QueueEvents::POST_CONSUME, new JobEvent($job));

        switch ($job->getStatus()) {
            case Job::STATUS_FAILED:
                $this->adapter->release($job);

                break;

            case Job::STATUS_REQUEUE:
                $this->requeue($job);

                break;

            default:
                $this->adapter->delete($job);

                break;
        }

        return true;
    }

    /**
     * Pushes a message to the adapter.
     *
     * @param Message $message
     */
    protected function doPush(Message $message)
    {
        $this->adapter->push($message);
        $this->dispatchEvent(QueueEvents::POST_PUSH, new MessageEvent($message));

        if ($this->isSynchronousAdapter()) {
            // consume the job right away
            $this->consume();
        }
    }

    /**
     * @param string $eventName
     * @param Event  $event
     */
    private function dispatchEvent($eventName, Event $event)
    {
        $this->dispatcher->dispatch($eventName, $event);

        $messages = $event->getMessages();

        if (count($messages) && $this->isSynchronousAdapter()) {
            throw new \RuntimeException('Cannot push messages in events with SynchronousAdapter.');
        }

        foreach ($messages as $message) {
            $this->doPush($message);
        }
    }

    /**
     * @param Job $job
     */
    private function requeue(Job $job)
    {
        if ($this->isSynchronousAdapter()) {
            throw new \RuntimeException('Cannot requeue jobs with SynchronousAdapter.');
        }

        $this->adapter->push($job->createRequeueMessage());
        $this->adapter->delete($job);
    }

    /**
     * @return bool
     */
    private function isSynchronousAdapter()
    {
        return $this->adapter instanceof SynchronousAdapter;
    }
}
