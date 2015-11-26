<?php

namespace Zenstruck\Queue\Driver;

use Aws\Sqs\SqsClient;
use Zenstruck\Queue\Driver;
use Zenstruck\Queue\Job;
use Zenstruck\Queue\Payload;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AmazonSqsDriver implements Driver
{
    private $client;
    private $queueUrl;

    /**
     * @param SqsClient $client
     * @param string    $queueUrl
     */
    public function __construct(SqsClient $client, $queueUrl)
    {
        $this->client = $client;
        $this->queueUrl = $queueUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function push(Payload $payload)
    {
        $this->client->sendMessage([
            'QueueUrl' => $this->queueUrl,
            'MessageBody' => json_encode($payload),
            'MessageAttributes' => [
                'metadata' => [
                    'StringValue' => $payload->metadata(),
                    'DataType' => 'String',
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $result = $this->client->receiveMessage([
            'QueueUrl' => $this->queueUrl,
            'MaxNumberOfMessages' => 1,
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        $rawPayload = $result->toArray();

        if (!isset($rawPayload['Messages'][0])) {
            return null;
        }

        $message = $rawPayload['Messages'][0];

        if (null === $payload = Payload::fromJson($message['Body'])) {
            // can't handle - requeue
            return null;
        }

        return new Job($payload, (int) $message['Attributes']['ApproximateReceiveCount'], $message['ReceiptHandle']);
    }

    /**
     * {@inheritdoc}
     */
    public function release(Job $job)
    {
        // noop - AmazonSQS auto releases jobs that aren't deleted.
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Job $job)
    {
        $this->client->deleteMessage([
            'QueueUrl' => $this->queueUrl,
            'ReceiptHandle' => $job->id(),
        ]);
    }
}
