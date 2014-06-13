<?php

namespace Zenstruck\Queue\Adapter;

use Aws\Sqs\SqsClient;
use Zenstruck\Queue\Adapter;
use Zenstruck\Queue\Message;
use Zenstruck\Queue\Job;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AmazonSqsAdapter implements Adapter
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
    public function push(Message $message)
    {
        $args = array(
            'QueueUrl'    => $this->queueUrl,
            'MessageBody' => base64_encode(serialize($message)),
            'MessageAttributes' => array(
                'info' => array(
                    'StringValue' => $message->getInfo(),
                    'DataType' => 'String'
                )
            )
        );

        if (null !== ($delay = $message->getDelay())) {
            $args['DelaySeconds'] = $delay;
        }

        $this->client->sendMessage($args);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $result = $this->client->receiveMessage(
            array(
                'QueueUrl' => $this->queueUrl,
                'MaxNumberOfMessages' => 1,
                'AttributeNames' => array('ApproximateReceiveCount')
            )
        );

        $rawMessage = $result->getPath('Messages/0');

        if (!$rawMessage) {
            return null;
        }

        $message = @unserialize(base64_decode($rawMessage['Body']));

        if (!$message instanceof Message) {
            // cannot handle, sqs auto-requeues
            return null;
        }

        return new Job($rawMessage['ReceiptHandle'], $message, $rawMessage['Attributes']['ApproximateReceiveCount']);
    }

    /**
     * {@inheritdoc}
     */
    public function release(Job $job)
    {
        // AmazonSQS auto releases jobs that aren't deleted.
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Job $job)
    {
        $this->client->deleteMessage(
            array(
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $job->getId()
            )
        );
    }
}
