<?php

namespace Zenstruck\Queue\Tests\Fixtures;

use Zenstruck\Queue\BaseConsumer;
use Zenstruck\Queue\Event\JobEvent;
use Zenstruck\Queue\Job;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class TestConsumer extends BaseConsumer
{
    const ACTION_DEFAULT   = 0;
    const ACTION_REQUEUE   = 1;
    const ACTION_FAIL      = 2;
    const ACTION_DELETE    = 3;
    const ACTION_EXCEPTION = 4;

    private $action;
    private $pushMessage;
    private $job = null;

    public function __construct($action = self::ACTION_DEFAULT, $pushMessage = false)
    {
        $this->action = $action;
        $this->pushMessage = $pushMessage;
    }

    /**
     * @return null|Job
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(JobEvent $event)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doConsume(JobEvent $event)
    {
        $this->job = $event->getJob();

        if ($this->pushMessage && $this->job->getData() !== 'test') {
            $event->push('test', 'test message');
        }

        switch ($this->action) {
            case self::ACTION_FAIL:
                $this->job->fail('Fail!');

                break;

            case self::ACTION_REQUEUE:
                $this->job->requeue();

                break;

            case self::ACTION_DELETE:
                $this->job->delete();

                break;

            case self::ACTION_EXCEPTION:
                throw new \RuntimeException('this has failed.');
        }
    }
}
