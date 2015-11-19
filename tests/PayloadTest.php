<?php

namespace Zenstruck\Queue\Tests;

use Zenstruck\Queue\Payload;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class PayloadTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_and_access_properties()
    {
        $payload = new Payload('serialized envelope', 'metadata');
        $this->assertSame('serialized envelope', $payload->serializedEnvelope());
        $this->assertSame('metadata', $payload->metadata());
    }

    /**
     * @test
     */
    public function can_json_encode_and_decode()
    {
        $payload = new Payload('serialized envelope', 'metadata');
        $this->assertEquals($payload, Payload::fromJson(json_encode($payload)));
    }

    /**
     * @test
     *
     * @dataProvider invalidJsonProvider
     */
    public function failed_from_json_returns_null($json)
    {
        $this->assertNull(Payload::fromJson($json));
    }

    public function invalidJsonProvider()
    {
        return [
            [null],
            [[]],
            ['sdsdsdsdsd'],
            [5],
            ['{}'],
            ['{"serialized_envelope":"valid","metadata":6}'],
            ['{"serialized_envelope":5,"metadata":"valid"}'],
        ];
    }
}
