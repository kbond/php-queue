<?php

namespace spec\Zenstruck\Queue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zenstruck\Queue\Message;

/**
 * @mixin Message
 */
class MessageSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('foo', 'foo message', array('bar' => 'baz'), 6);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Zenstruck\Queue\Message');
    }

    function its_properties_can_be_accessed()
    {
        $this->getData()->shouldBe('foo');
        $this->getInfo()->shouldBe('foo message');
        $this->getMetadata()->shouldBe(array('bar' => 'baz'));
        $this->getDelay()->shouldBe(6);
    }
}
