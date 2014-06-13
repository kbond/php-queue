<?php

namespace spec\Zenstruck\Queue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zenstruck\Queue\Listener;
use Zenstruck\Queue\Queue;

/**
 * @mixin Listener
 */
class ListenerSpec extends ObjectBehavior
{
    function let(Queue $queue)
    {
        $this->beConstructedWith($queue);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Zenstruck\Queue\Listener');
    }

    function it_cancels_after_max_jobs($queue)
    {
        $queue->consume()->willReturn(true);

        $this->listen(1)->shouldBe('Max jobs consumed.');
    }

    function it_cancels_after_timeout()
    {
        $this->listen(null, 0)->shouldBe('Timeout reached.');
    }

    function it_cancels_after_max_memory_reached()
    {
        $this->listen(null, null, 0)->shouldBe('Memory limit reached.');
    }
}
