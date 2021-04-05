<?php

namespace Local\Bundles\RequestLogBundle\Command;

use Local\Bundles\RequestLogBundle\Service\ResponseLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DumpRequestLogsCommand
 * @package Local\Bundles\RequestLogBundle\Command
 */
class DumpRequestLogsCommand extends Command
{
    /**
     * @var ResponseLogger $responseLogger Логгер ответов.
     */
    private $responseLogger;

    /**
     * DumpRequestLogsCommand constructor.
     *
     * @param ResponseLogger $responseLogger Логгер ответов.
     */
    public function __construct(ResponseLogger $responseLogger)
    {
        $this->responseLogger = $responseLogger;

        parent::__construct();
    }

    /**
     * @param string $mocksDir Относительный путь к директории с моками.
     *
     * @return void
     */
    public function setMocksDir(string $mocksDir): void
    {
        $this->responseLogger->setMocksDir($_SERVER['DOCUMENT_ROOT'] . $mocksDir);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this
            ->setName('mroca:request-log:dump')
            ->setDescription('Copy all responses mocks in another directory')
            ->addArgument('target_directory', InputArgument::REQUIRED, 'The mocks target directory');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetDirectory = (string)$input->getArgument('target_directory');

        $this->responseLogger->dumpMocksTo($targetDirectory);

        return 0;
    }
}
