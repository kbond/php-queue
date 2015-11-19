<?php

namespace Zenstruck\Queue\Tests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class JobTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_and_access_properties()
    {
        $job = $this->createJob();
        $this->assertSame('serialized foo', $job->serializedEnvelope());
        $this->assertSame('foo metadata', $job->metadata());
        $this->assertSame(1, $job->attempts());
        $this->assertSame(2, $job->id());
        $this->assertNull($job->failedException());
        $this->assertFalse($job->isFailed());
    }

    /**
     * @test
     */
    public function can_fail_job()
    {
        $exception = new \Exception();
        $job = $this->createJob();
        $job->fail($exception);
        $this->assertSame($exception, $job->failedException());
        $this->assertTrue($job->isFailed());
    }

    /**
     * @test
     */
    public function can_json_encode()
    {
        $job = $this->createJob();
        $this->assertSame('{"metadata":"foo metadata","id":2,"failed":false,"attempts":1}', json_encode($job));

        $job->fail(new \Exception());
        $this->assertSame('{"metadata":"foo metadata","id":2,"failed":true,"attempts":1}', json_encode($job));
    }
}
