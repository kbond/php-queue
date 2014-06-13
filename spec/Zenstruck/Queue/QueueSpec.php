<?php

namespace spec\Zenstruck\Queue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zenstruck\Queue\Adapter;
use Zenstruck\Queue\Queue;

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
}
