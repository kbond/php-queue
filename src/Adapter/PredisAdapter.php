<?php

namespace Zenstruck\Queue\Adapter;

use Predis\Client;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class PredisAdapter extends BaseRedisAdapter
{
    private $client;

    public function __construct(Client $client, $queueName)
    {
        $this->client = $client;

        parent::__construct($queueName);
    }

    /**
     * {@inheritdoc}
     */
    protected function lPop($queueName)
    {
        return $this->client->lpop($queueName);
    }

    /**
     * {@inheritdoc}
     */
    protected function zAdd($queueName, $delay, $payload)
    {
        $this->client->zadd($queueName, $delay, $payload);
    }

    /**
     * {@inheritdoc}
     */
    protected function rPush($queueName, $payload)
    {
        $this->client->rpush($queueName, $payload);
    }

    /**
     * {@inheritdoc}
     */
    protected function zRangeByScore($queueName, $start, $end)
    {
        return $this->client->zrangebyscore($queueName, $start, $end);
    }

    /**
     * {@inheritdoc}
     */
    protected function zRemRangeByScore($queueName, $start, $end)
    {
        $this->client->zremrangebyscore($queueName, $start, $end);
    }
}
