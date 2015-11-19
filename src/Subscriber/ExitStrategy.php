<?php

namespace Zenstruck\Queue\Subscriber;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface ExitStrategy
{
    /**
     * @param int $count
     *
     * @return bool
     */
    public function shouldExit($count);

    /**
     * @return string
     */
    public function getReason();
}
