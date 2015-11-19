<?php

namespace Zenstruck\Queue\Tests;

use SimpleBus\Message\Bus\MessageBus;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Subscriber;
use Zenstruck\Queue\Subscriber\ExitStrategy\MaxCountExitStrategy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SubscriberTest extends TestCase
{
    private $totalConsumed;
    private $jobs;

    public function setUp()
    {
        parent::setUp();

        $this->totalConsumed = 0;
        $this->jobs = [];
    }

    public function dummyCallback($totalConsumed, Job $job)
    {
        $this->totalConsumed = $totalConsumed;
        $this->jobs[] = $job;
    }

    /**
     * @test
     */
    public function can_consume_and_delete_job()
    {
        $job = $this->createJob();

        $driver = $this->mockDriver();
        $driver->expects($this->exactly(3))
            ->method('pop')
            ->willReturnOnConsecutiveCalls($job, null, $job);
        $driver->expects($this->exactly(2))
            ->method('delete')
            ->with($job);

        $envelopeConsumer = $this->mockSerializedEnvelopeConsumer();
        $envelopeConsumer->expects($this->exactly(2))
            ->method('consume')
            ->with('serialized foo');

        $subscriber = new Subscriber($driver, $envelopeConsumer);
        $reason = $subscriber->subscribe(new MaxCountExitStrategy(2), [$this, 'dummyCallback'], 1);
        $this->assertSame('Max jobs consumed.', $reason);
        $this->assertSame(2, $this->totalConsumed);
        $this->assertCount(2, $this->jobs);
        $this->assertFalse($this->jobs[0]->isFailed());
        $this->assertFalse($this->jobs[1]->isFailed());
    }

    /**
     * @test
     */
    public function can_handle_failed_jobs()
    {
        $job = $this->createJob();
        $exception = new \Exception('job failed');

        $driver = $this->mockDriver();
        $driver->expects($this->once())
            ->method('pop')
            ->willReturn($job);
        $driver->expects($this->never())
            ->method('delete');
        $driver->expects($this->once())
            ->method('release')
            ->with($job);

        $envelopeConsumer = $this->mockSerializedEnvelopeConsumer();
        $envelopeConsumer->expects($this->once())
            ->method('consume')
            ->with('serialized foo')
            ->willThrowException($exception);

        $subscriber = new Subscriber($driver, $envelopeConsumer);
        $reason = $subscriber->subscribe(new MaxCountExitStrategy(1), [$this, 'dummyCallback']);
        $this->assertSame('Max jobs consumed.', $reason);
        $this->assertSame(1, $this->totalConsumed);
        $this->assertCount(1, $this->jobs);
        $this->assertTrue($this->jobs[0]->isFailed());
        $this->assertSame($exception, $this->jobs[0]->failedException());
    }

    /**
     * @test
     */
    public function can_handle_failed_jobs_and_fire_event()
    {
        $job = $this->createJob();

        $driver = $this->mockDriver();
        $driver->expects($this->once())
            ->method('pop')
            ->willReturn($job);
        $driver->expects($this->never())
            ->method('delete');
        $driver->expects($this->once())
            ->method('release')
            ->with($job);

        $envelopeConsumer = $this->mockSerializedEnvelopeConsumer();
        $envelopeConsumer->expects($this->once())
            ->method('consume')
            ->with('serialized foo')
            ->willThrowException(new \Exception());

        $messageBus = $this->mockMessageBus();
        $messageBus->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf('Zenstruck\Queue\Event\FailedJob'));

        $subscriber = new Subscriber($driver, $envelopeConsumer, $messageBus);
        $subscriber->subscribe(new MaxCountExitStrategy(1));
    }

    /**
     * @test
     */
    public function can_handle_failed_jobs_that_reach_max_attempts()
    {
        $job = $this->createJob();
        $exception = new \Exception('job failed');

        $driver = $this->mockDriver();
        $driver->expects($this->once())
            ->method('pop')
            ->willReturn($job);
        $driver->expects($this->once())
            ->method('delete')
            ->with($job);
        $driver->expects($this->never())
            ->method('release');

        $envelopeConsumer = $this->mockSerializedEnvelopeConsumer();
        $envelopeConsumer->expects($this->once())
            ->method('consume')
            ->with('serialized foo')
            ->willThrowException($exception);

        $subscriber = new Subscriber($driver, $envelopeConsumer);
        $reason = $subscriber->subscribe(new MaxCountExitStrategy(1), [$this, 'dummyCallback'], null, 1);
        $this->assertSame('Max jobs consumed.', $reason);
        $this->assertSame(1, $this->totalConsumed);
        $this->assertCount(1, $this->jobs);
        $this->assertTrue($this->jobs[0]->isFailed());
        $this->assertSame($exception, $this->jobs[0]->failedException());
    }

    /**
     * @test
     */
    public function can_handle_failed_jobs_that_reach_max_attempts_and_fires_events()
    {
        $job = $this->createJob();

        $driver = $this->mockDriver();
        $driver->expects($this->once())
            ->method('pop')
            ->willReturn($job);
        $driver->expects($this->once())
            ->method('delete')
            ->with($job);

        $envelopeConsumer = $this->mockSerializedEnvelopeConsumer();
        $envelopeConsumer->expects($this->once())
            ->method('consume')
            ->with('serialized foo')
            ->willThrowException(new \Exception());

        $messageBus = $this->mockMessageBus();
        $messageBus->expects($this->exactly(2))
            ->method('handle')
            ->withConsecutive(
                [$this->isInstanceOf('Zenstruck\Queue\Event\FailedJob')],
                [$this->isInstanceOf('Zenstruck\Queue\Event\MaxAttemptsReached')]
            );

        $subscriber = new Subscriber($driver, $envelopeConsumer, $messageBus);
        $subscriber->subscribe(new MaxCountExitStrategy(1), null, null, 1);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageBus
     */
    private function mockMessageBus()
    {
        return $this->getMock('SimpleBus\Message\Bus\MessageBus');
    }
}
