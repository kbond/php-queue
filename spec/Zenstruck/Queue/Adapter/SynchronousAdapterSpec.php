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

        $this->shouldThrow(new \RuntimeException('Cannot release job "foo message" with SynchronousAdapter.'))->during('release', array($job));
    }

    function it_should_throw_fail_exception_on_release_when_job_is_failed()
    {
        $job = new Job('id', new Message('foo', 'foo message'));
        $job->fail('this failed');

        $this->shouldThrow(new \RuntimeException('Job "foo message" failed with message "this failed".'))->during('release', array($job));
    }
}
