<?php

namespace Zenstruck\Queue\Adapter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RedisAdapter extends BaseRedisAdapter
{
    private $client;

    public function __construct(\Redis $client, $queueName)
    {
        $this->client = $client;

        parent::__construct($queueName);
    }

    /**
     * {@inheritdoc}
     */
    protected function lPop($queueName)
    {
        $data = $this->client->lPop($queueName);

        return false === $data ? null : $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function zAdd($queueName, $delay, $payload)
    {
        $this->client->zAdd($queueName, $delay, $payload);
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
    protected function zRangeByScore($queueName, $start, $end)
    {
        return $this->client->zRangeByScore($queueName, $start, $end);
    }

    /**
     * {@inheritdoc}
     */
    protected function zRemRangeByScore($queueName, $start, $end)
    {
        $this->client->zRemRangeByScore($queueName, $start, $end);
    }
}
