<?php

namespace Zenstruck\Queue\Tests\Event;

use Zenstruck\Queue\Event\FailedJob;
use Zenstruck\Queue\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class FailedJobTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_and_access_properties()
    {
        $job = $this->createJob();
        $exception = new \Exception();
        $job->fail($exception);
        $event = new FailedJob($job);
        $this->assertSame($job, $event->job());
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
        (new FailedJob($this->createJob()));
    }

    /**
     * @test
     */
    public function has_message_name()
    {
        $this->assertSame('queue.failed_job', FailedJob::messageName());
    }
}
