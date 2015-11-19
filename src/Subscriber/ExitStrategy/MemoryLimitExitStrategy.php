<?php

namespace Zenstruck\Queue\Subscriber\ExitStrategy;

use Zenstruck\Queue\Subscriber\ExitStrategy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MemoryLimitExitStrategy implements ExitStrategy
{
    private $memoryLimit;

    /**
     * @param int $memoryLimit The memory limit in MB - will exit if exceeded
     */
    public function __construct($memoryLimit)
    {
        $this->memoryLimit = $memoryLimit * 1024 * 1024;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldExit($count)
    {
        if (memory_get_usage() >= $this->memoryLimit) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return 'Memory limit reached.';
    }
}
