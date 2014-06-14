<?php

namespace spec\Zenstruck\Queue\Adapter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zenstruck\Queue\Adapter\SynchronousAdapter;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Message;

/**
 * @mixin SynchronousAdapter
 */
class SynchronousAdapterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Zenstruck\Queue\Adapter\SynchronousAdapter');
    }

    function it_can_push_and_pop()
    {
        $message = new Message('foo', 'foo message');

        $this->push($message);

        $this->pop()->shouldBeAnInstanceOf('Zenstruck\Queue\Job');
        $this->pop()->shouldBe(null);
    }

    function it_should_throw_exception_on_release()
    {
        $job = new Job('id', new Message('foo', 'foo message'));

        $this->shouldThrow('\RuntimeException')->during('release', array($job));
    }
}
