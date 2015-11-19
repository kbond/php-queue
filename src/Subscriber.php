<?php

namespace Zenstruck\Queue;

use SimpleBus\Asynchronous\Consumer\SerializedEnvelopeConsumer;
use SimpleBus\Message\Bus\MessageBus;
use Zenstruck\Queue\Event\FailedJob;
use Zenstruck\Queue\Event\MaxAttemptsReached;
use Zenstruck\Queue\Subscriber\ExitStrategy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Subscriber
{
    private $driver;
    private $consumer;
    private $eventBus;

    public function __construct(Driver $driver, SerializedEnvelopeConsumer $consumer, MessageBus $eventBus = null)
    {
        $this->driver = $driver;
        $this->consumer = $consumer;
        $this->eventBus = $eventBus;
    }

    /**
     * @param ExitStrategy  $exitStrategy
     * @param callable|null $callback     Callback that is called after job is consumed with args: count, job
     * @param int|null      $waitTime     Time to wait before consuming another job
     * @param int           $maxAttempts  The number of times to attempt a job before marking as failed, 0 for unlimited
     *
     * @return string
     */
    public function subscribe(ExitStrategy $exitStrategy, callable $callback = null, $waitTime = null, $maxAttempts = 50)
    {
        $count = 0;

        while (!$exitStrategy->shouldExit($count)) {
            $job = $this->driver->pop();

            if ($job instanceof Job) {
                $this->consume($job, $maxAttempts);
                $this->executeCallback(++$count, $job, $callback);
            }

            if (null !== $waitTime) {
                sleep($waitTime);
            }
        }

        return $exitStrategy->getReason();
    }

    /**
     * @param int          $count
     * @param Job          $job
     * @param callable|int $callback
     */
    private function executeCallback($count, Job $job, callable $callback = null)
    {
        if (!is_callable($callback)) {
            return;
        }

        $callback($count, $job);
    }

    /**
     * @param Job $job
     * @param int $maxAttempts
     */
    private function consume(Job $job, $maxAttempts)
    {
        try {
            $this->consumer->consume($job->serializedEnvelope());
            $this->driver->delete($job);
        } catch (\Exception $exception) {
            $job->fail($exception);
            $this->handleFailedJob($job, $maxAttempts);
        }
    }

    /**
     * @param Job $job
     * @param int $maxAttempts
     */
    private function handleFailedJob(Job $job, $maxAttempts)
    {
        $this->handleEvent(new FailedJob($job));

        if (0 !== $maxAttempts && $job->attempts() >= $maxAttempts) {
            $this->handleEvent(new MaxAttemptsReached($job, $maxAttempts));
            $this->driver->delete($job);

            return;
        }

        $this->driver->release($job);
    }

    /**
     * @param object $event
     */
    private function handleEvent($event)
    {
        if (!$this->eventBus instanceof MessageBus) {
            return;
        }

        $this->eventBus->handle($event);
    }
}
