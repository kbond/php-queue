<?php

namespace spec\Zenstruck\Queue\Event;

use Prophecy\Argument;
use Zenstruck\Queue\Event\MessageEvent;
use Zenstruck\Queue\Message;

/**
 * @mixin MessageEvent
 */
class MessageEventSpec extends EventSpec
{
    function let()
    {
        $this->beConstructedWith(new Message('foo', 'foo message'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Zenstruck\Queue\Event\MessageEvent');
    }

    function it_can_get_the_message()
    {
        $this->getMessage()->shouldBeAnInstanceOf('Zenstruck\Queue\Message');
        $this->getMessage()->getData()->shouldBe('foo');
    }
}
