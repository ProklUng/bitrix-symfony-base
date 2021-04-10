<?php

namespace Local\Bundles\BitrixDatabaseBundle\Command;

use Exception;
use InvalidArgumentException;
use Local\Bundles\BitrixDatabaseBundle\Services\IblockDataGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\TruncaterElements;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SeedElementsCommand
 * @package Local\Bundles\BitrixDatabaseBundle\Command
 *
 * @since 08.04.2021
 */
class SeedElementsCommand extends Command
{
    /**
     * @const integer DEFAULT_QUANTITY_RECORD Количество записей, генерируемых по умолчанию.
     */
    private const DEFAULT_QUANTITY_RECORD = 4;

    /**
     * @const integer DEFAULT_QUANTITY_SECTIONS Количество подразделов, генерируемых по умолчанию.
     */
    private const DEFAULT_QUANTITY_SECTIONS = 2;

    /**
     * @var IblockDataGenerator $elementGenerator Генератор фикстур.
     */
    private $elementGenerator;

    /**
     * @var TruncaterElements $truncaterElements Очиститель инфоблока от элементов.
     */
    private $truncaterElements;

    /**
     * SeedElementsCommand constructor.
     *
     * @param IblockDataGenerator $fixtureGenerator  Генератор фикстур.
     * @param TruncaterElements   $truncaterElements Очиститель инфоблока от элементов.
     */
    public function __construct(
        IblockDataGenerator $fixtureGenerator,
        TruncaterElements $truncaterElements
    ) {
        $this->elementGenerator = $fixtureGenerator;
        $this->truncaterElements = $truncaterElements;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this->setName('migrator:elements')
             ->setDescription('Seed iblock of database from fixture.')
             ->addArgument('code', InputArgument::REQUIRED, 'Code of iblock')
             ->addArgument('type', InputArgument::OPTIONAL, 'Type of iblock', 'content')
             ->addOption('truncate', '', InputOption::VALUE_OPTIONAL, 'Truncate data of table', true)
             ->addOption('sections', '', InputOption::VALUE_OPTIONAL, 'Generate subsections?', false)
             ->addOption('count', '', InputOption::VALUE_OPTIONAL, 'Count of elements', static::DEFAULT_QUANTITY_RECORD);
    }

    /**
     * @inheritDoc
     * @throws Exception | InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->validateParameters($input);

        $count = (int)$input->getOption('count');
        $truncate = trim($input->getOption('truncate')) === 'true';
        $needSections = trim($input->getOption('sections')) === 'true';

        $code = $input->getArgument('code');
        $typeIblock = $input->getArgument('type');

        $output->writeln('Подготовка к созданию элементов в инфоблоке с кодом ' . $code);
        $this->elementGenerator->setIblockCode($code);
        $this->elementGenerator->setIblockType($typeIblock);

        if ($truncate) {
            $output->writeln('Очистка от старых элементов инфоблока');
            $this->truncaterElements->deleteElements(
                $this->elementGenerator->getIdIblock($code, $typeIblock)
            );

            if ($needSections) {
                $output->writeln('Удаление подразделов инфоблока');
                $this->elementGenerator->deleteAllSections();
            }
        }

        $sections = [];
        if ($needSections) {
            $output->writeln('Генерация подразделов');
            $sections = $this->elementGenerator->generateSections(static::DEFAULT_QUANTITY_SECTIONS);
            $output->writeln('Генерация подразделов завершена.');
        }

        $output->writeln('Создание элементов в инфоблоке с кодом ' . $code);

        for ($i = 1; $i<= $count; $i++) {
            $result[] = $this->elementGenerator->generate($sections);
        }

        $output->writeln('Элементы инфоблока с кодом ' . $code . ' успешно созданы.');

        return 0;
    }

    /**
     * Валидация входящих параметров.
     *
     * @param InputInterface $input
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function validateParameters(InputInterface $input) : void
    {
        if (!is_numeric($input->getOption('count'))) {
            throw new InvalidArgumentException(
                'Параметр count должен быть только числом.'
            );
        }

        if (!is_string($input->getOption('truncate'))) {
            throw new InvalidArgumentException(
                'Параметр table должен быть только строкой.'
            );
        }

        if (!is_string($input->getOption('sections'))) {
            throw new InvalidArgumentException(
                'Параметр truncate должен быть только строкой.'
            );
        }
    }
}
