<?php

namespace Local\Commands\Database;

use Dotenv\Dotenv;
use Exception;
use Local\Util\Database\Import;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class DbDrop
 * Дроп текущей базы.
 * @package Local\Util\Database\Commands
 *
 * @since 12.12.2020
 */
class DbDrop extends Command
{
    /**
     * @var QuestionHelper $question Помощник с вопросами.
     */
    private $questionHelper;

    /**
     * Конфигурация.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('db:drop')
             ->setDescription('Drop current database');
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->questionHelper = $this->getHelper('question');

        parent::initialize($input, $output);
    }

    /**
     * Исполнение команды.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dotenv = new Dotenv(realpath($_SERVER['DOCUMENT_ROOT']));
        $dotenv->load();

        $dbHost = $_ENV['DB_HOST'];
        $dbName = $_ENV['DB_NAME'];
        $dbLogin = $_ENV['DB_LOGIN'];
        $dbPassword = $_ENV['DB_PASSWORD'];

        if (!$dbHost || !$dbName || !$dbLogin) {
            $output->writeln('Env variables for database empty.');

            return;
        }

        $question = new ConfirmationQuestion(
            '    <error>You sure? Current database will be destroyed.</error>' . PHP_EOL
            . '    <info>Overwrite? [Y/n]</info> ',
            true,
            '/^(y|j)/i'
        );

        if (!$this->questionHelper->ask($input, $output, $question)) {
            return;
        }

        $importer = new Import(
            $dbHost,
            $dbName,
            $dbLogin,
            $dbPassword
        );

        try {
            $importer->init();
        } catch (Exception $e) {
            $output->writeln(sprintf(
                'Error connect to MySql server %s.',
                $e->getMessage()
            ));
        }

        // Дропинг базы.
        $output->writeln(sprintf(
            'Dropping database %s.',
            $dbName,
        ));

        $importer->dropDatabase($dbName);

        $output->writeln('Dropping database completed.');
    }
}