<?php

namespace Zenstruck\Queue\Tests\Functional;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Queue\Console\ListenCommand;
use Zenstruck\Queue\Listener;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ListenCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider executeProvider
     */
    public function testExecute(array $input, $message)
    {
        $queue = $this->getMock('Zenstruck\Queue\Queue', array(), array(), '', false);
        $queue->expects($this->any())->method('consume')->willReturn(true);
        $application = new Application();
        $application->add(new ListenCommand(new Listener($queue)));

        $command = $application->find('zenstruck:queue:listen');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array_merge(array('command' => $command->getName()), $input));

        $this->assertEquals($message, trim($commandTester->getDisplay()));
    }

    public function executeProvider()
    {
        return array(
            array(array('--timeout' => 0), 'Listener Stopped: Timeout reached.'),
            array(array('--memory-limit' => 0), 'Listener Stopped: Memory limit reached.'),
            array(array('--max-jobs' => 1), 'Listener Stopped: Max jobs consumed.'),
            array(array('--max-jobs' => 10), 'Listener Stopped: Max jobs consumed.'),
        );
    }
}
