<?php

namespace Zenstruck\Queue\Subscriber\ExitStrategy;

use Zenstruck\Queue\Subscriber\ExitStrategy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MaxCountExitStrategy implements ExitStrategy
{
    private $maxCount;

    /**
     * @param int $maxCount The number of jobs to consume before exiting
     */
    public function __construct($maxCount)
    {
        $this->maxCount = $maxCount;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldExit($count)
    {
        if ($count >= $this->maxCount) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return 'Max jobs consumed.';
    }
}
