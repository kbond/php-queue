<?php

namespace Zenstruck\Queue\Tests;

use SimpleBus\Asynchronous\Consumer\SerializedEnvelopeConsumer;
use Zenstruck\Queue\Driver;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Payload;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Driver
     */
    protected function mockDriver()
    {
        return $this->getMock('Zenstruck\Queue\Driver');
    }

    protected function createJob()
    {
        return new Job(new Payload('serialized foo', 'foo metadata'), 1, 2);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SerializedEnvelopeConsumer
     */
    protected function mockSerializedEnvelopeConsumer()
    {
        return $this->getMock('SimpleBus\Asynchronous\Consumer\SerializedEnvelopeConsumer');
    }
}
