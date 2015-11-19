<?php

namespace Zenstruck\Queue\Tests\Event;

use Zenstruck\Queue\Event\MaxAttemptsReached;
use Zenstruck\Queue\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MaxAttemptsReachedTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_and_access_properties()
    {
        $job = $this->createJob();
        $exception = new \Exception();
        $job->fail($exception);
        $event = new MaxAttemptsReached($job, 9);
        $this->assertSame($job, $event->job());
        $this->assertSame(9, $event->maxAttempts());
        $this->assertSame($exception, $event->exception());
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Job is not marked as failed.
     */
    public function cannot_create_with_successful_job()
    {
        (new MaxAttemptsReached($this->createJob(), 1));
    }

    /**
     * @test
     */
    public function has_message_name()
    {
        $this->assertSame('queue.max_attempts_reached', MaxAttemptsReached::messageName());
    }
}
