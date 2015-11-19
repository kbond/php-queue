<?php

namespace Zenstruck\Queue;

use SimpleBus\Asynchronous\Publisher\Publisher as SimpleBusPublisher;
use SimpleBus\Serialization\Envelope\Serializer\MessageInEnvelopSerializer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Publisher implements SimpleBusPublisher
{
    private $driver;
    private $serializer;

    public function __construct(Driver $driver, MessageInEnvelopSerializer $serializer)
    {
        $this->driver = $driver;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($message)
    {
        \Assert\that($message)->isObject();

        $this->driver->push(
            new Payload($this->serializer->wrapAndSerialize($message), $this->generateMetadata($message))
        );
    }

    /**
     * @param object $message
     *
     * @return string
     */
    private function generateMetadata($message)
    {
        $class = get_class($message);

        if ($message instanceof \JsonSerializable) {
            return sprintf('%s: %s', $class, json_encode($message));
        }

        if (method_exists($message, '__toString')) {
            return sprintf('%s: %s', $class, $message);
        }

        return $class;
    }
}
