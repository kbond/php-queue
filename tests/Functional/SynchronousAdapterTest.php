<?php

namespace Zenstruck\Queue\Tests\Functional;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Zenstruck\Queue\Adapter\SynchronousAdapter;
use Zenstruck\Queue\EventListener\LoggableSubscriber;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Message;
use Zenstruck\Queue\Queue;
use Zenstruck\Queue\QueueSpool;
use Zenstruck\Queue\Tests\Fixtures\TestConsumer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SynchronousAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testConsume()
    {
        $consumer = new TestConsumer();

        $queue = new Queue(new SynchronousAdapter(), new EventDispatcher());
        $queue->addConsumer($consumer);

        $this->assertFalse($queue->consume(), 'Empty queue');
        $this->assertNull($consumer->getJob(), 'Not yet set');

        $queue->push('foo', 'foo message');
        $this->assertSame('foo', $consumer->getJob()->getData());

        $this->assertFalse($queue->consume(), 'Empty queue');

        $queue->push('bar', 'bar message');
        $this->assertSame('bar', $consumer->getJob()->getData());

        $this->assertFalse($queue->consume(), 'Empty queue');
    }

    public function testLoggableSubscriber()
    {
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                array('PUSHING: "foo message"'),
                array('CONSUMING: "foo message"')
            )
        ;

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new LoggableSubscriber($logger));

        $queue = new Queue(new SynchronousAdapter(), $dispatcher);

        $queue->push('foo', 'foo message', array());
    }

    public function testQueueSpool()
    {
        $consumer = new TestConsumer();

        $queue = new QueueSpool(new SynchronousAdapter(), new EventDispatcher());
        $queue->addConsumer($consumer);

        $this->assertFalse($queue->consume(), 'Empty queue');
        $this->assertNull($consumer->getJob(), 'Not yet set');

        $queue->push('foo', 'foo message', array());
        $queue->push('bar', 'foo message', array());

        $this->assertNull($consumer->getJob(), 'Not yet set');

        $queue->flush();

        $this->assertSame('bar', $consumer->getJob()->getData());
        $this->assertFalse($queue->consume());

        $queue->flush();

        $this->assertFalse($queue->consume(), 'Spool is empty');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Job "foo message" failed with message "Fail!"
     */
    public function testFailException()
    {
        $consumer = new TestConsumer(TestConsumer::ACTION_FAIL);

        $queue = new Queue(new SynchronousAdapter(), new EventDispatcher());
        $queue->addConsumer($consumer);

        $queue->push('foo', 'foo message', array());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot requeue jobs with SynchronousAdapter.
     */
    public function testRequeueException()
    {
        $consumer = new TestConsumer(TestConsumer::ACTION_REQUEUE);

        $queue = new Queue(new SynchronousAdapter(), new EventDispatcher());
        $queue->addConsumer($consumer);

        $queue->push('foo', 'foo message', array());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Job "foo message" failed with message "Cannot push messages in events with SynchronousAdapter.".
     */
    public function testEventPushException()
    {
        $consumer = new TestConsumer(TestConsumer::ACTION_DEFAULT, true);

        $queue = new Queue(new SynchronousAdapter(), new EventDispatcher());
        $queue->addConsumer($consumer);

        $queue->push('foo', 'foo message', array());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot release job "foo message" with SynchronousAdapter.
     */
    public function testReleaseException()
    {
        $adapter = new SynchronousAdapter();

        $adapter->release(new Job('foo', new Message('foo', 'foo message')));
    }
}
