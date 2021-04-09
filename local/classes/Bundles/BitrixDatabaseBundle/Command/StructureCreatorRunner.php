<?php

namespace Local\Bundles\BitrixDatabaseBundle\Command;

use Exception;
use Local\Commands\Runner\CommandRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Class StructureCreatorRunner
 * @package Local\Bundles\BitrixDatabaseBundle\Command
 *
 * @since 09.04.2021
 */
class StructureCreatorRunner extends Command
{
    /** @var SymfonyStyle */
    protected $io;

    /**
     * @var array $configRunner Команды, создающие структуру проекта.
     */
    private $configRunner;

    /**
     * StructureCreatorRunner constructor.
     *
     * @param array $configRunner Команды, создающие структуру проекта.
     */
    public function __construct(array $configRunner)
    {
        $this->configRunner = $configRunner;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('migrator:structure')
             ->setDescription('Creating structure of project');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->writeln('Creating structure of project');

        $params = [];
        foreach ($this->configRunner as $item) {
            $tokens = explode(' ', $item);
            $params[] = new Process([...$tokens]);
        }

        (new CommandRunner($params))
            ->continueOnError(true)
            ->setIO($this->io)
            ->setLimit(3)
            ->run();

        return 0;

    }
}