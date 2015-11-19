<?php

namespace Zenstruck\Queue\Bridge\Symfony\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zenstruck\Queue\Subscriber;
use Zenstruck\Queue\Subscriber\ExitStrategy\ChainExitStrategy;
use Zenstruck\Queue\Subscriber\ExitStrategy\MaxCountExitStrategy;
use Zenstruck\Queue\Subscriber\ExitStrategy\MemoryLimitExitStrategy;
use Zenstruck\Queue\Subscriber\ExitStrategy\TimeoutExitStrategy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SubscriberCommand extends Command
{
    private $subscriber;

    /**
     * @param Subscriber $subscriber
     * @param string     $name
     * @param string     $description
     */
    public function __construct(Subscriber $subscriber, $name, $description)
    {
        parent::__construct($name);
        $this->setDescription($description);

        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument('max-attempts', InputArgument::OPTIONAL, 'The number of times to attempt a job before marking as failed, 0 for unlimited', 50)
            ->addOption('wait-time', null, InputOption::VALUE_REQUIRED, 'Time in seconds to wait before consuming another job')
            ->addOption('max-jobs', null, InputOption::VALUE_REQUIRED, 'The number of jobs to consume before exiting')
            ->addOption('timeout', null, InputOption::VALUE_REQUIRED, 'The number of seconds to consume jobs before exiting')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'The memory limit in MB - will exit if exceeded')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exitStrategy = new ChainExitStrategy();

        if (null !== $maxJobs = $input->getOption('max-jobs')) {
            $exitStrategy->addExitStrategy(new MaxCountExitStrategy($maxJobs));
        }

        if (null !== $timeout = $input->getOption('timeout')) {
            $exitStrategy->addExitStrategy(new TimeoutExitStrategy($timeout));
        }

        if (null !== $memoryLimit = $input->getOption('memory-limit')) {
            $exitStrategy->addExitStrategy(new MemoryLimitExitStrategy($memoryLimit));
        }

        $reason = $this->subscriber->subscribe($exitStrategy, null, $input->getOption('wait-time'), $input->getArgument('max-attempts'));
        $output->writeln(sprintf('<comment>Subscriber Exited</comment>: %s', $reason));
    }
}
