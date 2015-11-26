<?php

namespace Zenstruck\Queue\Driver;

use Predis\ClientInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PredisDriver extends BaseRedisDriver
{
    private $client;

    /**
     * @param ClientInterface $client
     * @param string          $queueName
     */
    public function __construct(ClientInterface $client, $queueName)
    {
        $this->client = $client;

        parent::__construct($queueName);
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
    protected function lPop($queueName)
    {
        return $this->client->lpop($queueName);
    }
}
