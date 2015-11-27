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
use Zenstruck\Queue\SubscriberRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SubscriberCommand extends Command
{
    private $subscriberRegistry;

    /**
     * @param SubscriberRegistry $subscriberRegistry
     */
    public function __construct(SubscriberRegistry $subscriberRegistry)
    {
        parent::__construct();

        $this->subscriberRegistry = $subscriberRegistry;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zenstruck:queue:subscribe')
            ->setDescription('Subscribe to a registered queue')
            ->addArgument('subscriber', InputArgument::OPTIONAL, 'The name of the subscriber to listen to')
            ->addOption('max-attempts', null, InputOption::VALUE_REQUIRED, 'The number of times to attempt a job before marking as failed, 0 for unlimited', 50)
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
        $subscriberName = $input->getArgument('subscriber');
        $exitStrategy = new ChainExitStrategy();

        if (!$subscriberName) {
            return $this->listSubscribers($output);
        }

        if (null !== $maxJobs = $input->getOption('max-jobs')) {
            $exitStrategy->addExitStrategy(new MaxCountExitStrategy($maxJobs));
        }

        if (null !== $timeout = $input->getOption('timeout')) {
            $exitStrategy->addExitStrategy(new TimeoutExitStrategy($timeout));
        }

        if (null !== $memoryLimit = $input->getOption('memory-limit')) {
            $exitStrategy->addExitStrategy(new MemoryLimitExitStrategy($memoryLimit));
        }

        $subscriber = $this->subscriberRegistry->get($subscriberName);
        $reason = $subscriber->subscribe($exitStrategy, null, $input->getOption('wait-time'), $input->getOption('max-attempts'));
        $output->writeln(sprintf('<comment>Subscriber Exited</comment>: %s', $reason));

        return 0;
    }

    private function listSubscribers(OutputInterface $output)
    {
        $output->writeln('<info>Available Subscribers:</info>');

        foreach (array_keys($this->subscriberRegistry->all()) as $name) {
            $output->writeln(sprintf(' - %s', $name));
        }

        return 0;
    }
}
