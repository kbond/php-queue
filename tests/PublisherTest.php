<?php

namespace Zenstruck\Queue\Tests;

use Zenstruck\Queue\Payload;
use Zenstruck\Queue\Publisher;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class PublisherTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider messageProvider
     */
    public function can_publish_message($message, Payload $payload)
    {
        $serializer = $this->getMock('SimpleBus\Serialization\Envelope\Serializer\MessageInEnvelopSerializer');
        $serializer->expects($this->once())
            ->method('wrapAndSerialize')
            ->with($message)
            ->willReturn('serialized message');

        $driver = $this->mockDriver();
        $driver->expects($this->once())
            ->method('push')
            ->with($payload);

        $publisher = new Publisher($driver, $serializer);
        $publisher->publish($message);
    }

    public function messageProvider()
    {
        return [
            [new \stdClass(), new Payload('serialized message', 'stdClass')],
            [new ToStringMessage(), new Payload('serialized message', 'Zenstruck\Queue\Tests\ToStringMessage: a string')],
            [new JsonSerializableMessage(), new Payload('serialized message', 'Zenstruck\Queue\Tests\JsonSerializableMessage: {"foo":"bar"}')],
        ];
    }
}

class ToStringMessage
{
    public function __toString()
    {
        return 'a string';
    }
}

class JsonSerializableMessage implements \JsonSerializable
{
    public function jsonSerialize()
    {
        return ['foo' => 'bar'];
    }
}
