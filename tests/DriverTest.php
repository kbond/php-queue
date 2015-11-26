<?php

namespace Zenstruck\Queue\Tests;

use Zenstruck\Queue\Driver;
use Zenstruck\Queue\Payload;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class DriverTest extends TestCase
{
    /**
     * @test
     */
    public function integration_lifecycle()
    {
        $driver = $this->createDriver();
        $this->assertNull($driver->pop());

        $driver->push(new Payload('envelope1', 'metadata1'));
        $this->pushInvalidData();
        $driver->push(new Payload('envelope2', 'metadata2'));

        $job = $driver->pop();
        $this->assertSame('envelope1', $job->serializedEnvelope());
        $this->assertSame('metadata1', $job->metadata());
        $this->assertSame(1, $job->attempts());
        $driver->delete($job);

        do {
            $job = $driver->pop();
        } while (null === $job);

        $this->assertSame('envelope2', $job->serializedEnvelope());
        $this->assertSame('metadata2', $job->metadata());
        $this->assertSame(1, $job->attempts());
        $driver->release($job);

        do {
            $job = $driver->pop();
        } while (null === $job);

        $this->assertSame('envelope2', $job->serializedEnvelope());
        $this->assertSame('metadata2', $job->metadata());
        $this->assertSame(2, $job->attempts());
        $driver->delete($job);

        $this->assertNull($driver->pop());
        $this->assertNull($driver->pop());
        $this->assertNull($driver->pop());
    }

    /**
     * @return Driver
     */
    abstract public function createDriver();

    abstract public function pushInvalidData();
}
