<?php

namespace Zenstruck\Queue\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Zenstruck\Queue\Message;
use Zenstruck\Queue\Pushable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Event extends BaseEvent implements Pushable
{
    /**
     * @var Message[]
     */
    private $messages = array();

    /**
     * {@inheritdoc}
     */
    public function push($data, $info, array $metadata = array(), $delay = null)
    {
        $this->messages[] = new Message($data, $info, $metadata, $delay);
    }

    /**
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
