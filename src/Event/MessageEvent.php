<?php

namespace Zenstruck\Queue\Event;

use Zenstruck\Queue\Message;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MessageEvent extends Event
{
    private $message;

    /**
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
