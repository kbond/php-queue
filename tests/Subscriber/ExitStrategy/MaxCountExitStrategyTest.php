<?php

namespace Zenstruck\Queue\Tests\Subscriber\ExitStrategy;

use Zenstruck\Queue\Subscriber\ExitStrategy\MaxCountExitStrategy;
use Zenstruck\Queue\Tests\Subscriber\ExitStrategyTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MaxCountExitStrategyTest extends ExitStrategyTest
{
    protected function createExitStrategyThatShouldExit()
    {
        return new MaxCountExitStrategy(3);
    }

    protected function createExitStrategyThatShouldNotExit()
    {
        return new MaxCountExitStrategy(10);
    }

    protected function expectedReason()
    {
        return 'Max jobs consumed.';
    }
}
