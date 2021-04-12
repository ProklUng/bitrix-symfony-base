<?php

namespace Local\Bundles\BitrixDatabaseBundle\Command;

use Exception;
use InvalidArgumentException;
use Local\Bundles\BitrixDatabaseBundle\Services\UserGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SeedUsersCommand
 * @package Local\Bundles\BitrixDatabaseBundle\Command
 *
 * @since 12.04.2021
 */
class SeedUsersCommand extends Command
{
    /**
     * @const integer DEFAULT_QUANTITY_RECORD Количество записей, генерируемых по умолчанию.
     */
    private const DEFAULT_QUANTITY_RECORD = 5;

    /**
     * @var UserGenerator $userGenerator Сидер пользователей.
     */
    private $userGenerator;

    /**
     * SeedUsersCommand constructor.
     *
     * @param UserGenerator $userGenerator
     */
    public function __construct(
        UserGenerator $userGenerator
    ) {
        $this->userGenerator = $userGenerator;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this->setName('migrator:users')
             ->setDescription('Seed random users.')
             ->addOption('count', '',InputOption::VALUE_OPTIONAL, 'Count of users', static::DEFAULT_QUANTITY_RECORD)
             ->addOption('truncate', '', InputOption::VALUE_OPTIONAL, 'Truncate of users', true)
             ->addOption('phone', '', InputOption::VALUE_OPTIONAL, 'Phone as login', true);
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
        $phoneable = $input->getOption('phone') === 'true';

        $output->writeln('Starting generating users.');

        if ($truncate) {
            $output->writeln('Clearing users.');
            $this->userGenerator->deleteAllUsers();
        }

        for ($i = 0; $i < $count; $i++) {
            $this->userGenerator->createUser($phoneable);
        }

        $output->writeln('Generate users completed.');

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

        if (!is_string($input->getOption('phone'))) {
            throw new InvalidArgumentException(
                'Параметр count должен быть только строкой.'
            );
        }
    }
}
