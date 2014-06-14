<?php

namespace Zenstruck\Queue\Tests\Fixtures;

use Zenstruck\Queue\Consumer;
use Zenstruck\Queue\Event\JobEvent;
use Zenstruck\Queue\Job;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class TestConsumer implements Consumer
{
    const ACTION_DEFAULT = 0;
    const ACTION_REQUEUE = 1;
    const ACTION_FAIL    = 2;

    private $action;
    private $job = null;

    public function __construct($action = self::ACTION_DEFAULT)
    {
        $this->action = $action;
    }

    /**
     * {@inheritdoc}
     */
    public function consume(JobEvent $event)
    {
        $this->job = $event->getJob();

        switch ($this->action) {
            case self::ACTION_FAIL:
                $this->job->fail('Fail!');

                break;

            case self::ACTION_REQUEUE:
                $this->job->requeue();

                break;
        }
    }

    /**
     * @return null|Job
     */
    public function getJob()
    {
        return $this->job;
    }
}
