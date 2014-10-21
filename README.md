# Queue

[![Build Status](http://img.shields.io/travis/kbond/Queue.svg?style=flat-square)](https://travis-ci.org/kbond/Queue)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/kbond/Queue.svg?style=flat-square)](https://scrutinizer-ci.com/g/kbond/Queue/)
[![Code Coverage](http://img.shields.io/scrutinizer/coverage/g/kbond/Queue.svg?style=flat-square)](https://scrutinizer-ci.com/g/kbond/Queue/)
[![Latest Stable Version](http://img.shields.io/packagist/v/zenstruck/queue.svg?style=flat-square)](https://packagist.org/packages/zenstruck/queue)
[![License](http://img.shields.io/packagist/l/zenstruck/queue.svg?style=flat-square)](https://packagist.org/packages/zenstruck/queue)

Unified API for different queue services. Heavily inspired by
[Laravel Queue](http://laravel.com/docs/4.2/queues).

This library provides a queue manager that pushes "messages" and consumes "jobs".

## Installation

    composer require zenstruck\queue

## Usage

### Create a Queue

```php
use Symfony\Component\EventDispatcher\EventDispatcher;
use Zenstruck\Queue\Queue;

$adapter = // ... see adapters section below
$dispatcher = new EventDispatcher(); // ... see events section below
$failUnknown = // true/false ... see see below
$queue = new Queue($adapter, $dispatcher, $failUnknown);
```

When consuming jobs, you have the ability to set its status to one of `failed`, `requeue`
or `delete`. If a status is not set, it is considered `unknown`.  The `$failUnknown` option
sets whether jobs whose status isn't implicitly set are set considered failed.  Defaults to
`false`.

### Push Data to the Queue

```php
$queue->push($data, $info, $metadata, $delay);
```

Parameter   | Type     | Description                                             | Default
----------- | -------- | ------------------------------------------------------- | ----------
`$data`     | mixed    | The data to push to the queue                           | *required*
`$info`     | string   | Information message about the push (useful for logging) | *required*
`$metadata` | array    | Additional data about the message                       | `array()`
`$delay`    | int|null | Delay in seconds (null for adapter default)             | `null`

### Consume a Job from the Queue

```php
$ret = $queue->consume();
```

Returns `true` if a job was consumed, `false` otherwise.

### Add a Job Consumer

1. Create a consumer:

    ```php
    use Zenstruck\Queue\BaseConsumer;
    use Zenstruck\Queue\Event\JobEvent;

    class MyConsumer extends BaseConsumer
    {
        public function supports(JobEvent $event)
        {
            $job = $event->getJob();

            // return true if can consume job
        }

        public function doConsume(JobEvent $event)
        {
            $job = $event->getJob();

            $data = $job->getData(); // get message data
            $info = $job->getInfo(); // get message info
            $metadata = $job->getMetadata(); // get message metadata
            $attempts = $job->getAttempts(); // the number of times this job has been attempted

            // consume this job...

            // set job status
            $job->delete(); // consume was successful, delete
            $job->requeue(10); // requeue the job with a 10 second delay
            $job->fail('this job failed', 10); // fail the job with a reason and a 10 second delay

            $event->push('message', 'message info'); // can push new messages to the queue
        }
    }
    ```

    **NOTE**: When a job is failed, it is requeued and its attempt count is increased.

2. Add consumer to queue:

    ```php
    $queue->addConsumer(new MyConsumer(), 10); // add with a priority of 10
    ```

## Appendix

### A. Adapters

#### SynchronousAdapter

This is a special adapter that has its job consumed as soon as it is pushed. This
can be useful for the development environment or a post response flush listener
(ie `kernel.terminate` event in Symfony2).

```php
use Zenstruck\Queue\Adapter\SynchronousAdapter;

$adapter = new SynchronousAdapter();
```

There are some restrictions when using the SynchronousAdapter because of how easy it would
be to enter an infinite loop. An exception is thrown for these cases:

1. Jobs marked as failed.
2. Jobs marked as requeue.
3. Pushing messages within events.

#### BeanstalkdAdapter

Requires [pda/pheanstalk](https://github.com/pda/pheanstalk).

    composer require pda/pheanstalk

```php
use Zenstruck\Queue\Adapter\BeanstalkdAdapter;

$client = new \Pheanstalk_Pheanstalk('localhost');

$adapter = new BeanstalkdAdapter($client, 'my_queue');
```

#### AmazonSqsAdapter

Requires [aws/aws-sdk-php](https://github.com/aws/aws-sdk-php).

    composer require aws/aws-sdk-php

```php
use Aws\Sqs\SqsClient;
use Zenstruck\Queue\Adapter\AmazonSqsAdapter;

$client = SqsClient::factory(array(
    'key'    => 'foo',
    'secret' => 'bar',
    'region' => 'us-east-1'
));

$adapter = new AmazonSqsAdapter($client, 'https://sqs.us-east-1.amazonaws.com/queue/url');
```

#### RedisAdapter

Requires the [Redis PHP extension](https://github.com/nicolasff/phpredis).

```php
use Zenstruck\Queue\Adapter\RedisAdapter;

$client = new \Redis();
$client->connect('localhost');

$adapter = new RedisAdapter($client, 'my_queue');
```

#### PredisAdapter

Requires [predis/predis](https://github.com/nrk/predis).

    composer require predis/predis

```php
use Predis\Client;
use Zenstruck\Queue\Adapter\PredisAdapter;

$client = new Client();

$adapter = new PredisAdapter($client, 'my_queue');
```

### B. Listener

A listener class is provided for consuming jobs from the queue.

```php
use Zenstruck\Queue\Listener;
use Zenstruck\Queue\Queue;

$queue = new Queue(/* ... */);
$listener = new Listener($queue);

$listener->listen(); // run indefinitely
$listener->listen(10); // run until 10 jobs are consumed
$listener->listen(null, 60); // run for 60 seconds
$listener->listen(null, null, 64); // run until 64MB of memory is used
```

### C. Listener Command

A Symfony2 Console command is provided as a wrapper for the listener.

    composer require symfony/console

For setting up a command line application, see the
[Console documentation](http://symfony.com/doc/current/components/console/introduction.html).

```php
use Symfony\Component\Console\Application;
use Zenstruck\Queue\Console\ListenCommand;
use Zenstruck\Queue\Queue;
use Zenstruck\Queue\Listener;

$queue = new Queue(/* ... */);
$listener = new Listener($queue);
$command = new ListenCommand($listener);
$application = new Application();

$application->addCommand($command);
```

```
Usage:
 zenstruck:queue:listen [--max-jobs="..."] [--timeout="..."] [--memory-limit="..."]

Options:
 --max-jobs            The number of jobs to consume before exiting
 --timeout             The number of seconds to consume jobs before exiting
 --memory-limit        The memory limit in MB - will exit if exceeded
```

### D. Events

There are several events that you can hook into. For more information on using the event
dispatcher, see its [documentation](http://symfony.com/doc/current/components/event_dispatcher/introduction.html).

Name                           | Description                                   | Event Class
------------------------------ | --------------------------------------------- | ------------------------------------
`zenstruck_queue.post_push`    | Occurs after a message is pushed to the queue | `Zenstruck\Queue\Event\MessageEvent`
`zenstruck_queue.pre_consume`  | Occurs before job is consumed                 | `Zenstruck\Queue\Event\JobEvent`
`zenstruck_queue.consume`      | The main consume event (consumers use this)   | `Zenstruck\Queue\Event\JobEvent`
`zenstruck_queue.post_consume` | Occurs after a job is consumed                | `Zenstruck\Queue\Event\JobEvent`

**NOTE**: You can push additional messages to your queue using `MessageEvent::push()`
and `JobEvent::push(). Be careful using this as it can lead to infinite recursion.

### E. LoggableSubscriber

This library comes with a PSR-3 Loggable Event Subscriber. It is a good example of how to use the
above events.

```php
use Symfony\Component\EventDispatcher\EventDispatcher;
use Zenstruck\Queue\EventListener\LoggableSubscriber;
use Zenstruck\Queue\Queue;

$logger = // ... a PSR-3 logger such as Monolog
$subscriber = new LoggableSubscriber($logger);
$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber($subscriber);

$queue = new Queue(/* adapter */, $dispatcher);
```

### F. QueueSpool

When using the standard `Queue`, pushing a message immediately pushes it to the adapter. The `QueueSpool`
extends `Queue` but pushed messages are added to an "in memory spool". When the spool is flushed, then
all messages are added to the adapter queue. This can be useful if your adapter is slow or you are using the
`SynchronousAdapter`.  You can flush the spool after the response is sent to the user.

```php
$queue = new QueueSpool(/* ... */);

$queue->push(/* ... */);
$queue->push(/* ... */);
$queue->push(/* ... */);

// send response to user

$queue->flush(); // flush the spool and send messages to adapter queue
```
