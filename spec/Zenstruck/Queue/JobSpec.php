<?php

namespace spec\Zenstruck\Queue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Message;

/**
 * @mixin Job
 */
class JobSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('id', new Message('foo', 'foo message', array('bar' => 'baz')));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Zenstruck\Queue\Job');
    }

    function its_properties_can_be_accessed()
    {
        $this->getId()->shouldBe('id');
        $this->getData()->shouldBe('foo');
        $this->getInfo()->shouldBe('foo message');
        $this->getMetadata()->shouldBe(array('bar' => 'baz'));
    }

    function it_can_create_a_requeue_message()
    {
        $message = $this->createRequeueMessage();
        $message->shouldBeAnInstanceOf('Zenstruck\Queue\Message');
        $message->getData()->shouldBe('foo');
        $message->getInfo()->shouldBe('foo message');
        $message->getMetadata()->shouldBe(array('bar' => 'baz'));
    }

    function its_status_is_unknown_by_default()
    {
        $this->getStatus()->shouldBe(Job::STATUS_UNKNOWN);
    }

    function it_can_be_marked_as_failed()
    {
        $this->fail('fail message', 5);

        $this->getStatus()->shouldBe(Job::STATUS_FAILED);
        $this->getFailMessage()->shouldBe('fail message');
        $this->createRequeueMessage()->getDelay()->shouldBe(5);
    }

    function it_can_be_marked_as_delete()
    {
        $this->delete();

        $this->getStatus()->shouldBe(Job::STATUS_DELETE);
    }

    function it_can_be_marked_as_requeue()
    {
        $this->requeue(2);

        $this->getStatus()->shouldBe(Job::STATUS_REQUEUE);
        $this->createRequeueMessage()->getDelay()->shouldBe(2);
    }
}
