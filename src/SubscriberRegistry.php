<?php

namespace Zenstruck\Queue;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SubscriberRegistry
{
    private $subscribers = [];

    /**
     * @param array|Subscriber[] $subscribers
     */
    public function __construct(array $subscribers = [])
    {
        foreach ($subscribers as $name => $subscriber) {
            $this->add($name, $subscriber);
        }
    }

    /**
     * @param string     $name
     * @param Subscriber $subscriber
     */
    public function add($name, Subscriber $subscriber)
    {
        $this->subscribers[$name] = $subscriber;
    }

    /**
     * @return array|Subscriber[]
     */
    public function all()
    {
        return $this->subscribers;
    }

    /**
     * @param string $name
     *
     * @return Subscriber
     *
     * @throws \InvalidArgumentException
     */
    public function get($name)
    {
        if (!isset($this->subscribers[$name])) {
            throw new \InvalidArgumentException(sprintf('Subscriber "%s" not found.', $name));
        }

        return $this->subscribers[$name];
    }
}
