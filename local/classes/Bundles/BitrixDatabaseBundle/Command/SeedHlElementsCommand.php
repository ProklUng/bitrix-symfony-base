<?php

namespace Local\Bundles\BitrixDatabaseBundle\Command;

use Exception;
use Local\Bundles\BitrixDatabaseBundle\Services\IblockHlDataGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\HighloadBlock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SeedHlElementsCommand
 * @package Local\Bundles\BitrixDatabaseBundle\Command
 *
 * @since 10.04.2021
 */
class SeedHlElementsCommand extends Command
{
    /**
     * @const integer DEFAULT_QUANTITY_RECORD Количество записей, генерируемых по умолчанию.
     */
    private const DEFAULT_QUANTITY_RECORD = 4;

    /**
     * @var IblockHlDataGenerator $elementGenerator Генератор фикстур.
     */
    private $elementGenerator;

    /**
     * @var HighloadBlock $highloadBlock
     */
    private $highloadBlock;

    /**
     * SeedHlElementsCommand constructor.
     *
     * @param IblockHlDataGenerator $fixtureGenerator Генератор фикстур.
     * @param HighloadBlock         $highloadBlock
     */
    public function __construct(
        IblockHlDataGenerator $fixtureGenerator,
        HighloadBlock $highloadBlock
    ) {
        $this->elementGenerator = $fixtureGenerator;
        $this->highloadBlock = $highloadBlock;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this->setName('migrator:elements-hl')
             ->setDescription('Seed high-load block of database from fixture.')
             ->addArgument('code', InputArgument::REQUIRED, 'Name of hl-iblock')
             ->addOption('truncate', '', InputOption::VALUE_OPTIONAL, 'Truncate data of table', true)
             ->addOption('count', '', InputOption::VALUE_OPTIONAL, 'Count of elements', static::DEFAULT_QUANTITY_RECORD);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $truncate = trim($input->getOption('truncate')) === 'true';
        $count = (int)$input->getOption('count');
        $code = $input->getArgument('code');

        $output->writeln('Подготовка к созданию элементов в hl-инфоблоке с кодом ' . $code);
        $this->elementGenerator->setIblockCode($code);

        if ($truncate) {
            $output->writeln('Очистка от старых элементов hl-инфоблока');
            $this->highloadBlock->deleteElements($code);
        }

        $output->writeln('Создание элементов в hl-инфоблоке с кодом ' . $code);

        for ($i = 1; $i<= $count; $i++) {
            $result[] = $this->elementGenerator->generate();
        }

        $output->writeln('Элементы hl-инфоблока с кодом ' . $code . ' успешно созданы.');

        return 0;
    }
}
