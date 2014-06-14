<?php

namespace spec\Zenstruck\Queue\Event;

use Prophecy\Argument;
use Zenstruck\Queue\Event\JobEvent;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Message;

/**
 * @mixin JobEvent
 */
class JobEventSpec extends EventSpec
{
    function let()
    {
        $this->beConstructedWith(new Job('id', new Message('foo', 'foo message')));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Zenstruck\Queue\Event\JobEvent');
    }

    function it_can_get_the_job()
    {
        $this->getJob()->shouldBeAnInstanceOf('Zenstruck\Queue\Job');
        $this->getJob()->getId()->shouldBe('id');
    }
}
