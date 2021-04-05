<?php

namespace Local\Commands\Runner;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Class ExampleRunner
 * @package Local\Commands\Runner
 *
 * @since 02.04.2021
 */
class ExampleRunner extends Command
{
    /** @var SymfonyStyle */
    protected $io;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('runner:example')
             ->setDescription('runner example');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->writeln('Running runner example');

        sleep(5); # Sleep so user can abort update

        (new CommandRunner([
            new Process(['cache:clear', 'cache:clear --cache-type menu']),
        ]))
            ->continueOnError(true)
            ->setIO($this->io)
            ->setLimit(3)
            ->run();

        return 0;
    }
}