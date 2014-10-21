<?php

namespace Zenstruck\Queue;

/**
 * Pushes messages to an in-memory spool to be flushed later.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class QueueSpool extends Queue
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
     * Flush the spool - push spooled messages onto adapter.
     */
    public function flush()
    {
        foreach ($this->messages as $key => $message) {
            $this->doPush($message);
        }

        $this->messages = array();
    }
}
