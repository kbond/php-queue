<?php

namespace Zenstruck\Queue\Subscriber\ExitStrategy;

use Zenstruck\Queue\Subscriber\ExitStrategy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class TimeoutExitStrategy implements ExitStrategy
{
    private $exitTime;

    /**
     * @param int $timeout The number of seconds to consume jobs before exiting
     */
    public function __construct($timeout)
    {
        $this->exitTime = time() + $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldExit($count)
    {
        if (time() >= $this->exitTime) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return 'Timeout reached.';
    }
}
