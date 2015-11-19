<?php

namespace Zenstruck\Queue\Tests\Subscriber;

use Zenstruck\Queue\Subscriber\ExitStrategy;
use Zenstruck\Queue\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class ExitStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function should_exit()
    {
        $this->assertTrue($this->createExitStrategyThatShouldExit()->shouldExit(5));
    }

    /**
     * @test
     */
    public function should_not_exit()
    {
        $this->assertFalse($this->createExitStrategyThatShouldNotExit()->shouldExit(5));
    }

    /**
     * @test
     */
    public function has_expected_reason()
    {
        $this->assertSame($this->expectedReason(), $this->createExitStrategyThatShouldExit()->getReason());
    }

    /**
     * @return ExitStrategy
     */
    abstract protected function createExitStrategyThatShouldExit();

    /**
     * @return ExitStrategy
     */
    abstract protected function createExitStrategyThatShouldNotExit();

    /**
     * @return string
     */
    abstract protected function expectedReason();
}
