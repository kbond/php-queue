<?php

namespace Zenstruck\Queue\Tests\Bridge\Symfony\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Queue\Bridge\Symfony\Console\SubscriberCommand;
use Zenstruck\Queue\Subscriber;
use Zenstruck\Queue\Tests\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SubscriberCommandTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider argumentProvider
     */
    public function command_exits_with_proper_reason($arguments, $expectedReason)
    {
        $tester = $this->createCommandTester();
        $tester->execute(array_merge(['command' => 'my:command'], $arguments));
        $this->assertContains($expectedReason, $tester->getDisplay());
    }

    public function argumentProvider()
    {
        return [
            [['--timeout' => 0], 'Timeout reached.'],
            [['--max-jobs' => 0], 'Max jobs consumed.'],
            [['--memory-limit' => 0], 'Memory limit reached.'],
            [['--timeout' => 10, '--max-jobs' => 10, '--memory-limit' => 0], 'Memory limit reached.'],
        ];
    }

    private function createCommandTester()
    {
        $subscriber = new Subscriber($this->mockDriver(), $this->mockSerializedEnvelopeConsumer());
        $application = new Application();
        $application->add(new SubscriberCommand($subscriber, 'my:command', 'my command description'));

        return new CommandTester($application->find('my:command'));
    }
}
