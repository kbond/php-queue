<?php

namespace Zenstruck\Queue\EventHandler;

use Psr\Log\LoggerInterface;
use Zenstruck\Queue\Event\FailedJob;
use Zenstruck\Queue\Event\MaxAttemptsReached;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class LoggerHandler
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handleFailedJob(FailedJob $event)
    {
        $this->logger->error(
            sprintf('Job failed with message "%s"', $event->exception()->getMessage()),
            ['job' => $event->job()]
        );
    }

    public function handleMaxAttemptsReached(MaxAttemptsReached $event)
    {
        $this->logger->error(
            sprintf('Max attempts of %d reached trying to consume job', $event->maxAttempts()),
            ['job' => $event->job()]
        );
    }
}
