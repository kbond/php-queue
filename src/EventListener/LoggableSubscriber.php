<?php

namespace Zenstruck\Queue\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zenstruck\Queue\Event\JobEvent;
use Zenstruck\Queue\Event\MessageEvent;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\QueueEvents;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class LoggableSubscriber implements EventSubscriberInterface
{
    private $logger;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            QueueEvents::POST_PUSH => array('postPush', -1024),
            QueueEvents::PRE_CONSUME => array('preConsume', -1024),
            QueueEvents::POST_CONSUME => array('postConsume', -1024),
        );
    }

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param MessageEvent $event
     */
    public function postPush(MessageEvent $event)
    {
        $this->logger->info(sprintf('PUSHING: "%s"', $event->getMessage()->getInfo()));
    }

    /**
     * @param JobEvent $event
     */
    public function preConsume(JobEvent $event)
    {
        $this->logger->info(sprintf('CONSUMING: "%s"', $event->getJob()->getInfo()));
    }

    /**
     * @param JobEvent $event
     */
    public function postConsume(JobEvent $event)
    {
        $job = $event->getJob();

        switch ($job->getStatus()) {
            case Job::STATUS_FAILED:
                $this->logger->error(
                    sprintf('FAILED CONSUMING: "%s", REASON: "%s"', $job->getInfo(), $job->getFailMessage()),
                    array('attempts' => $job->getAttempts())
                );
                break;

            case Job::STATUS_REQUEUE:
                $this->logger->info(sprintf('REQUEUING: "%s"', $job->getInfo()));
                break;
        }
    }
}
