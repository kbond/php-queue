<?php

namespace Zenstruck\Queue\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zenstruck\Queue\Listener;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ListenCommand extends Command
{
    private $listener;

    public function __construct(Listener $listener)
    {
        parent::__construct();

        $this->listener = $listener;
    }

    protected function configure()
    {
        $this
            ->setName('zenstruck:queue:listen')
            ->setDescription('Listen to and consume jobs from a queue.')
            ->addOption('max-jobs', null, InputOption::VALUE_OPTIONAL, 'The number of jobs to consume before exiting')
            ->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'The number of seconds to consume jobs before exiting')
            ->addOption('memory-limit', null, InputOption::VALUE_OPTIONAL, 'The memory limit in MB - will exit if exceeded')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ret = $this->listener->listen(
            $input->getOption('max-jobs'),
            $input->getOption('timeout'),
            $input->getOption('memory-limit')
        );

        $output->writeln(sprintf('<comment>Listener Stopped</comment>: %s', $ret));
    }
}
