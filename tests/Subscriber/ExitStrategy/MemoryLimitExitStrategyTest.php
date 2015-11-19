<?php

namespace Zenstruck\Queue\Tests\Subscriber\ExitStrategy;

use Zenstruck\Queue\Subscriber\ExitStrategy\MemoryLimitExitStrategy;
use Zenstruck\Queue\Tests\Subscriber\ExitStrategyTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MemoryLimitExitStrategyTest extends ExitStrategyTest
{
    protected function createExitStrategyThatShouldExit()
    {
        return new MemoryLimitExitStrategy(0);
    }

    protected function createExitStrategyThatShouldNotExit()
    {
        return new MemoryLimitExitStrategy(1000000000);
    }

    protected function expectedReason()
    {
        return 'Memory limit reached.';
    }
}
