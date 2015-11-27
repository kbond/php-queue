<?php

namespace Zenstruck\Queue\Tests\Bridge\Symfony\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Queue\Bridge\Symfony\Console\SubscriberCommand;
use Zenstruck\Queue\Subscriber;
use Zenstruck\Queue\SubscriberRegistry;
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
        $tester->execute(array_merge([
                'command' => 'zenstruck:queue:subscribe',
                'subscriber' => 'foo-subscribers',
            ],
            $arguments
        ));
        $this->assertContains($expectedReason, $tester->getDisplay());
    }

    /**
     * @test
     */
    public function can_list_available_subscribers_with_no_arguments()
    {
        $tester = $this->createCommandTester();
        $tester->execute(['command' => 'zenstruck:queue:subscribe']);
        $this->assertContains('Available Subscribers', $tester->getDisplay());
        $this->assertContains('- foo-subscribers', $tester->getDisplay());
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
        $application->add(new SubscriberCommand(new SubscriberRegistry(['foo-subscribers' => $subscriber])));

        return new CommandTester($application->find('zenstruck:queue:subscribe'));
    }
}
