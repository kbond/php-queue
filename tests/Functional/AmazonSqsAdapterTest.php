<?php

namespace Zenstruck\Queue\Tests\Functional;

use Aws\Sqs\SqsClient;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use Zenstruck\Queue\Adapter\AmazonSqsAdapter;
use Zenstruck\Queue\Message;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AmazonSqsAdapterTest extends BaseFunctionalTest
{
    /**
     * @var SqsClient
     */
    private $client;
    private $mockResponses = array();

    public function testConsume()
    {
        $this->addNullMockResponse(); // consume nothing
        $this->addNullMockResponse(); // pushing foo
        $this->addNullMockResponse(); // pushing bar
        $this->addMessageMockResponse(new Message('foo', 'foo message')); // receive foo
        $this->addNullMockResponse(); // delete foo
        $this->addNullMockResponse(); // consume invalid
        $this->addMessageMockResponse(new Message('bar', 'bar message')); // receive bar
        $this->addNullMockResponse(); // delete bar
        $this->addNullMockResponse(); // consume nothing

        parent::testConsume();
    }

    public function testConsumeWithDelay()
    {
        $this->addNullMockResponse(); // pushing foo
        $this->addNullMockResponse(); // pushing bar
        $this->addMessageMockResponse(new Message('bar', 'bar message')); // receive bar
        $this->addNullMockResponse(); // delete bar
        $this->addNullMockResponse(); // consume nothing
        $this->addMessageMockResponse(new Message('foo', 'foo message')); // receive foo
        $this->addNullMockResponse(); // delete foo
        $this->addNullMockResponse(); // consume nothing

        parent::testConsumeWithDelay();
    }

    public function testAttempts()
    {
        $this->addNullMockResponse(); // pushing foo
        $this->addMessageMockResponse(new Message('foo', 'foo message')); // receive foo
        $this->addMessageMockResponse(new Message('foo', 'foo message'), 2); // receive foo again

        parent::testAttempts();
    }

    public function testLoggableSubscriber()
    {
        $this->addNullMockResponse(); // pushing foo
        $this->addMessageMockResponse(new Message('foo', 'foo message')); // receive foo
        $this->addNullMockResponse(); // delete foo

        parent::testLoggableSubscriber();
    }

    public function testLoggableSubscriberWithRequeue()
    {
        $this->addNullMockResponse(); // pushing foo
        $this->addMessageMockResponse(new Message('foo', 'foo message')); // receive foo
        $this->addNullMockResponse(); // delete foo
        $this->addNullMockResponse(); // pushing foo

        parent::testLoggableSubscriberWithRequeue();
    }

    public function testLoggableSubscriberWithFail()
    {
        $this->addNullMockResponse(); // pushing foo
        $this->addMessageMockResponse(new Message('foo', 'foo message')); // receive foo

        parent::testLoggableSubscriberWithFail();
    }

    public function testQueueSpool()
    {
        $this->addNullMockResponse(); // consume nothing
        $this->addNullMockResponse(); // pushing foo
        $this->addNullMockResponse(); // pushing bar
        $this->addNullMockResponse(); // consume nothing
        $this->addMessageMockResponse(new Message('foo', 'foo message')); // receive foo
        $this->addNullMockResponse(); // delete foo
        $this->addMessageMockResponse(new Message('bar', 'bar message')); // receive bar
        $this->addNullMockResponse(); // delete bar
        $this->addNullMockResponse(); // consume nothing
        $this->addNullMockResponse(); // consume nothing

        parent::testQueueSpool();
    }


    protected function setUp()
    {
        $this->client = SqsClient::factory(array(
            'key'    => 'foo',
            'secret' => 'bar',
            'region' => 'us-east-1'
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function createAdapter()
    {
        $this->client->addSubscriber(new MockPlugin($this->mockResponses));

        return new AmazonSqsAdapter($this->client, 'http://foo.com');
    }

    /**
     * {@inheritdoc}
     */
    protected function pushInvalidData()
    {
        // noop
    }

    private function addNullMockResponse()
    {
        $this->mockResponses[] = Response::fromMessage("HTTP/1.1 200 OK\r\nContent-Type: application/xml\r\n\r\n");
    }

    private function addMessageMockResponse($body, $receiveCount = 1)
    {
        if ($body instanceof Message) {
            $body = base64_encode(serialize($body));
        }

        $md5 = md5($body);
        $this->mockResponses[] = Response::fromMessage("HTTP/1.1 200 OK\r\nContent-Type: application/xml\r\n\r\n" .
            "<ReceiveMessageResponse>
              <ReceiveMessageResult>
                <Message>
                  <MD5OfBody>" . $md5 ."</MD5OfBody>
                  <Body>". $body ."</Body>
                  <ReceiptHandle>foo</ReceiptHandle>
                  <Attributes>
                    <ApproximateReceiveCount>" . $receiveCount ."</ApproximateReceiveCount>
                  </Attributes>
                </Message>
              </ReceiveMessageResult>
            </ReceiveMessageResponse>"
        );
    }
}
