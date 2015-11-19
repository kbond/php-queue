<?php

namespace Zenstruck\Queue\Tests\Subscriber\ExitStrategy;

use Zenstruck\Queue\Subscriber\ExitStrategy\ChainExitStrategy;
use Zenstruck\Queue\Subscriber\ExitStrategy\MaxCountExitStrategy;
use Zenstruck\Queue\Subscriber\ExitStrategy\MemoryLimitExitStrategy;
use Zenstruck\Queue\Subscriber\ExitStrategy\TimeoutExitStrategy;
use Zenstruck\Queue\Tests\Subscriber\ExitStrategyTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ChainExitStrategyTest extends ExitStrategyTest
{
    /**
     * @test
     */
    public function exit_reason_should_match_exit_strategy()
    {
        $strategy = $this->createExitStrategyThatShouldExit();
        $strategy->shouldExit(5);
        $this->assertSame('Timeout reached.', $strategy->getReason());
    }

    protected function createExitStrategyThatShouldExit()
    {
        return new ChainExitStrategy([
            new MaxCountExitStrategy(10),
            new MemoryLimitExitStrategy(10000000),
            new TimeoutExitStrategy(0),
        ]);
    }

    protected function createExitStrategyThatShouldNotExit()
    {
        return new ChainExitStrategy([
            new MaxCountExitStrategy(10),
            new MemoryLimitExitStrategy(10000000),
            new TimeoutExitStrategy(10),
        ]);
    }

    protected function expectedReason()
    {
        return null;
    }
}
