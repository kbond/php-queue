<?php

namespace Zenstruck\Queue\Tests\Subscriber\ExitStrategy;

use Zenstruck\Queue\Subscriber\ExitStrategy\TimeoutExitStrategy;
use Zenstruck\Queue\Tests\Subscriber\ExitStrategyTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class TimeoutExitStrategyTest extends ExitStrategyTest
{
    protected function createExitStrategyThatShouldExit()
    {
        return new TimeoutExitStrategy(0);
    }

    protected function createExitStrategyThatShouldNotExit()
    {
        return new TimeoutExitStrategy(10);
    }

    protected function expectedReason()
    {
        return 'Timeout reached.';
    }
}
