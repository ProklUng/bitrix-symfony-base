<?php

namespace Local\Commands\Database;

use Dotenv\Dotenv;
use Exception;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ifsnop\Mysqldump as IMysqldump;

/**
 * Class DbExport
 * Дамп базы данных.
 * @package Local\Util\Database\Commands
 *
 * @since 13.12.2020
 */
class DbExport extends Command
{
    /**
     * Конфигурация.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('db:export')
            ->setDescription('Dump database')
            ->addArgument(
                'database_dump',
                InputArgument::OPTIONAL,
                'Path to database dump',
                '/'
            );
    }

    /**
     * Исполнение команды.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return integer
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

            return 1;
        }

        $output->writeln(sprintf(
            'Backuping of database %s.',
            $dbName,
        ));

        $backupDatabaseName = $this->exportDatabase(
            $dbHost,
            $dbName,
            $dbLogin,
            $dbPassword,
            $output
        );

        if (!$backupDatabaseName) {
            return 1;
        }

        $output->writeln('Export dump to database completed.');

        return 0;
    }

    /**
     * Экспорт базы.
     *
     * @param string $dbHost
     * @param string $dbName
     * @param string $dbLogin
     * @param string $dbPassword
     * @param OutputInterface $output
     *
     * @return string
     */
    private function exportDatabase(
        string $dbHost,
        string $dbName,
        string $dbLogin,
        string $dbPassword,
        OutputInterface $output
    ) : string {
        try {
            $dumpSettings = [
                'compress' => IMysqldump\Mysqldump::NONE,
                'no-data' => false,
                'add-drop-table' => true,
                'single-transaction' => true,
                'lock-tables' => true,
                'add-locks' => true,
                'extended-insert' => true,
                'disable-foreign-keys-check' => true,
                'skip-triggers' => false,
                'add-drop-trigger' => true,
                'databases' => true,
                'add-drop-database' => true,
                'hex-blob' => true,
            ];

            $dump = new IMysqldump\Mysqldump(
                "mysql:host=$dbHost;dbname=$dbName",
                $dbLogin,
                $dbPassword,
                $dumpSettings
            );

            @unlink($_SERVER['DOCUMENT_ROOT'].'/' . $dbName . '.sql');

            $dump->start($_SERVER['DOCUMENT_ROOT'] .'/' .$dbName. '.sql');
        } catch (\Exception $e) {
            echo 'mysqldump-php error: '.$e->getMessage();
            $output->writeln(sprintf(
                'Error backuping of database: %s',
                $e->getMessage()
            ));

            return '';
        }

        return '/' .$dbName. '.sql';
    }
}