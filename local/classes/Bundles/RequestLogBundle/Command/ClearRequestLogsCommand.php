<?php

namespace Local\Bundles\RequestLogBundle\Command;

use Local\Bundles\RequestLogBundle\Service\ResponseLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearRequestLogsCommand
 * @package Local\Bundles\RequestLogBundle\Command
 */
class ClearRequestLogsCommand extends Command
{
    /**
     * @var ResponseLogger $responseLogger Логгер ответов.
     */
    private $responseLogger;

    /**
     * ClearRequestLogsCommand constructor.
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
            ->setName('mroca:request-log:clear')
            ->setDescription('Empty the requests mocks directory');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->responseLogger->clearMocksDir();

        return 0;
    }
}
