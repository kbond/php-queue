<?php

namespace spec\Zenstruck\Queue\Event;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

abstract class EventSpec extends ObjectBehavior
{
    function it_can_push_messages()
    {
        $this->getMessages()->shouldHaveCount(0);

        $this->push('bar', 'bar message');

        $this->getMessages()->shouldHaveCount(1);
        $this->getMessages()[0]->shouldBeAnInstanceOf('Zenstruck\Queue\Message');
    }
}
