<?php

namespace spec\Zenstruck\Queue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zenstruck\Queue\Adapter;
use Zenstruck\Queue\Adapter\SynchronousAdapter;
use Zenstruck\Queue\Consumer;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Message;
use Zenstruck\Queue\Queue;
use Zenstruck\Queue\QueueEvents;

/**
 * @mixin Queue
 */
class QueueSpec extends ObjectBehavior
{
    function let(Adapter $adapter, EventDispatcherInterface $dispatcher)
    {
        $this->beConstructedWith($adapter, $dispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Zenstruck\Queue\Queue');
    }

    function it_should_implement_pushable_interface()
    {
        $this->shouldHaveType('Zenstruck\Queue\Pushable');
    }

    function it_can_add_consumers($dispatcher, Consumer $consumer)
    {
        $dispatcher
            ->addListener(QueueEvents::CONSUME, array($consumer, 'consume'), 10)
            ->shouldBeCalled()
        ;

        $this->addConsumer($consumer, 10);
    }

    function it_can_push_a_message($adapter, $dispatcher)
    {
        $adapter->push(Argument::type('Zenstruck\Queue\Message'))->shouldBeCalled();
        $adapter->pop()->shouldNotBeCalled();

        $dispatcher->dispatch(QueueEvents::POST_PUSH, Argument::type('Zenstruck\Queue\Event\MessageEvent'))
            ->shouldBeCalled()
        ;

        $this->push('foo', 'foo message');
    }

    function it_consumes_the_message_immediately_after_push_for_synchronous_adapter(SynchronousAdapter $adapter, $dispatcher)
    {
        $this->beConstructedWith($adapter, $dispatcher);
        $job = $this->create_test_job();

        $adapter->push(Argument::type('Zenstruck\Queue\Message'))->shouldBeCalled();
        $adapter->pop()->shouldBeCalled()->willReturn($job);
        $adapter->delete($job)->shouldBeCalled();

        $dispatcher->dispatch(QueueEvents::POST_PUSH, Argument::type('Zenstruck\Queue\Event\MessageEvent'))
            ->shouldBeCalled()
        ;

        $this->consume_events_called($dispatcher);

        $this->push('foo', 'foo message');
    }

    function it_should_return_false_when_no_job_is_consumed()
    {
        $this->consume()->shouldBe(false);
    }

    function it_should_return_true_when_a_job_is_consumed($adapter)
    {
        $job = $this->create_test_job();

        $adapter->pop()->shouldBeCalled()->willReturn($job);
        $adapter->delete($job)->shouldBeCalled();

        $this->consume()->shouldBe(true);
    }

    function it_should_dispatch_events_during_consume($adapter, $dispatcher)
    {
        $job = $this->create_test_job();

        $adapter->pop()->shouldBeCalled()->willReturn($job);
        $adapter->delete($job)->shouldBeCalled();

        $this->consume_events_called($dispatcher);

        $this->consume();
    }

    function it_should_fail_and_release_the_job_if_an_exception_is_thrown_during_consume_event($adapter, $dispatcher)
    {
        $job = $this->create_test_job();

        $adapter->pop()->shouldBeCalled()->willReturn($job);
        $adapter->release($job)->shouldBeCalled();
        $adapter->delete($job)->shouldNotBeCalled();

        $dispatcher->dispatch(Argument::type('string'), Argument::type('Zenstruck\Queue\Event\JobEvent'))
            ->shouldBeCalled()
        ;

        $dispatcher->dispatch(QueueEvents::CONSUME, Argument::type('Zenstruck\Queue\Event\JobEvent'))
            ->willThrow('\Exception')
        ;

        $this->consume();
    }

    function it_should_requeue_jobs_during_consume($adapter)
    {
        $job = $this->create_test_job();
        $job->requeue();

        $adapter->pop()->shouldBeCalled()->willReturn($job);
        $adapter->push(Argument::type('Zenstruck\Queue\Message'))->shouldBeCalled();
        $adapter->delete($job)->shouldBeCalled();

        $this->consume();
    }

    function it_fails_unknown_jobs_when_fail_unknown_is_enabled($adapter, $dispatcher)
    {
        $this->beConstructedWith($adapter, $dispatcher, true);

        $job = $this->create_test_job();

        $adapter->pop()->shouldBeCalled()->willReturn($job);
        $adapter->release($job)->shouldBeCalled();
        $adapter->delete($job)->shouldNotBeCalled();

        $this->consume();
    }

    function consume_events_called($dispatcher)
    {
        $dispatcher->dispatch(QueueEvents::PRE_CONSUME, Argument::type('Zenstruck\Queue\Event\JobEvent'))
            ->shouldBeCalled()
        ;

        $dispatcher->dispatch(QueueEvents::CONSUME, Argument::type('Zenstruck\Queue\Event\JobEvent'))
            ->shouldBeCalled()
        ;

        $dispatcher->dispatch(QueueEvents::POST_CONSUME, Argument::type('Zenstruck\Queue\Event\JobEvent'))
            ->shouldBeCalled()
        ;
    }

    function create_test_job()
    {
        return new Job('id', new Message('foo', 'foo message', array()), 1);
    }
}
