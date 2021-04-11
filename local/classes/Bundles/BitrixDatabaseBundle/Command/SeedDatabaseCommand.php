<?php

namespace Local\Bundles\BitrixDatabaseBundle\Command;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Loader;
use Exception;
use InvalidArgumentException;
use Local\Bundles\BitrixDatabaseBundle\Services\FixtureGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\SeedDatabase;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class SeedDatabaseCommand
 * @package Local\Bundles\BitrixDatabaseBundle\Command
 *
 * @since 08.04.2021
 */
class SeedDatabaseCommand extends Command
{
    /**
     * @const integer DEFAULT_QUANTITY_RECORD Количество записей, генерируемых по умолчанию.
     */
    private const DEFAULT_QUANTITY_RECORD = 5;

    /**
     * @var ServiceLocator $entityLocator Локатор с сущносятми таблиц.
     */
    private $entityLocator;

    /**
     * @var FixtureGenerator $fixtureGenerator Генератор фикстур.
     */
    private $fixtureGenerator;

    /**
     * @var SeedDatabase $seederDatabase Сидер базы данных.
     */
    private $seederDatabase;

    /**
     * SeedDatabaseCommand constructor.
     *
     * @param FixtureGenerator $fixtureGenerator Генератор фикстур.
     * @param SeedDatabase     $seederDatabase   Сидер базы данных.
     * @param ServiceLocator   $entityLocator    Локатор сущностей.
     */
    public function __construct(
        FixtureGenerator $fixtureGenerator,
        SeedDatabase $seederDatabase,
        ServiceLocator $entityLocator
    ) {
        $this->entityLocator = $entityLocator;
        $this->fixtureGenerator = $fixtureGenerator;
        $this->seederDatabase = $seederDatabase;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this->setName('migrator:seed')
             ->setDescription('Seed table of database from fixture.')
             ->addArgument('table', InputArgument::REQUIRED, 'Table of database')
             ->addOption('count', '',InputOption::VALUE_OPTIONAL, 'Count of records', static::DEFAULT_QUANTITY_RECORD)
             ->addOption('truncate', '', InputOption::VALUE_OPTIONAL, 'Truncate data of table', true);
    }

    /**
     * @inheritDoc
     * @throws Exception | InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->validateParameters($input);

        $count = (int)$input->getOption('count');
        $truncate = $input->getOption('truncate') === 'true';
        $table = $input->getArgument('table');

        $output->writeln('Looking for entity of table ' . $table);
        $entity = $this->locateEntityData($table);

        $fixture = $this->fixtureGenerator->fromSchema(
            $entity,
            $count
        );

        $output->writeln('Starting seeding database.');

        $this->seederDatabase->setPrefix('');
        $this->seederDatabase->setTable($table);

        if ($truncate) {
            $this->seederDatabase->truncate();
        }

        $this->seederDatabase->fromFixture($fixture);

        $output->writeln('Seeding table ' . $table .  ' of database completed.');

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
        if (!is_string($input->getArgument('table'))) {
            throw new InvalidArgumentException('Параметр table должен быть только строкой.');
        }

        if (!is_numeric($input->getOption('count'))) {
            throw new InvalidArgumentException(
                'Параметр count должен быть только числом.'
            );
        }
    }

    /**
     * Поиск сущности в сервис-локаторе.
     *
     * @param string $table Таблица.
     *
     * @return DataManager
     * @throws LogicException
     *
     */
    private function locateEntityData(string $table) : DataManager
    {
        foreach ($this->entityLocator->getProvidedServices() as $serviceId => $value) {
            /** @var DataManager $service */
            $service = $this->entityLocator->get($serviceId);
            if ($service->getTableName() === $table) {
                return $service;
            }
        }

        throw new LogicException(
            'Not found entity for table ' . $table
        );
    }
}
