<?php

namespace Zenstruck\Queue\Tests\EventHandler;

use Psr\Log\LoggerInterface;
use Zenstruck\Queue\Event\FailedJob;
use Zenstruck\Queue\Event\MaxAttemptsReached;
use Zenstruck\Queue\EventHandler\LoggerHandler;
use Zenstruck\Queue\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class LoggerHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function can_handle_failed_job_event()
    {
        $job = $this->createJob();
        $job->fail(new \Exception('failure'));

        $logger = $this->mockLogger();
        $logger->expects($this->once())
            ->method('error')
            ->with('Job failed with message "failure"', ['job' => $job]);

        (new LoggerHandler($logger))->handleFailedJob(new FailedJob($job));
    }

    /**
     * @test
     */
    public function can_handle_max_attempts_reached_event()
    {
        $job = $this->createJob();
        $job->fail(new \Exception('failure'));

        $logger = $this->mockLogger();
        $logger->expects($this->once())
            ->method('error')
            ->with('Max attempts of 5 reached trying to consume job', ['job' => $job]);

        (new LoggerHandler($logger))->handleMaxAttemptsReached(new MaxAttemptsReached($job, 5));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private function mockLogger()
    {
        return $this->getMock('Psr\Log\LoggerInterface');
    }
}
