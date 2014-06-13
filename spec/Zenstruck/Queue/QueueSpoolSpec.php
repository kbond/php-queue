<?php

namespace spec\Zenstruck\Queue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zenstruck\Queue\Adapter;
use Zenstruck\Queue\QueueSpool;

/**
 * @mixin QueueSpool
 */
class QueueSpoolSpec extends ObjectBehavior
{
    function let(Adapter $adapter, EventDispatcherInterface $dispatcher)
    {
        $this->beConstructedWith($adapter, $dispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Zenstruck\Queue\QueueSpool');
    }

    function it_should_extend_queue()
    {
        $this->shouldHaveType('Zenstruck\Queue\Queue');
    }

    function it_does_not_push_to_adapter_before_flush($adapter)
    {
        $adapter->push()->shouldNotBeCalled();

        $this->push('foo', 'foo message');
        $this->push('bar', 'bar message');
    }

    function it_flushes_messages($adapter)
    {
        $adapter->push(Argument::type('Zenstruck\Queue\Message'))->shouldBeCalledTimes(2);

        $this->push('foo', 'foo message');
        $this->push('bar', 'bar message');
        $this->flush();
    }
}
