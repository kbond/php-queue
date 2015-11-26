<?php

namespace Zenstruck\Queue\Driver;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RedisDriver extends BaseRedisDriver
{
    private $client;

    /**
     * @param \Redis $client
     * @param string $queueName
     */
    public function __construct(\Redis $client, $queueName)
    {
        $this->client = $client;

        parent::__construct($queueName);
    }

    /**
     * {@inheritdoc}
     */
    protected function rPush($queueName, $payload)
    {
        $this->client->rPush($queueName, $payload);
    }

    /**
     * {@inheritdoc}
     */
    protected function lPop($queueName)
    {
        $data = $this->client->lPop($queueName);

        return false === $data ? null : $data;
    }
}
