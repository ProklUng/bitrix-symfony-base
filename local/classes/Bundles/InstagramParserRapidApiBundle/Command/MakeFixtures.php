<?php

namespace Local\Bundles\InstagramParserRapidApiBundle\Command;

use Exception;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Exceptions\InstagramTransportException;
use Local\Bundles\InstagramParserRapidApiBundle\Services\Interfaces\RetrieverInstagramDataInterface;
use Local\Bundles\InstagramParserRapidApiBundle\Services\UserInfoRetriever;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MakeFixtures
 * Создание фикстур Инстаграма.
 * @package Local\Bundles\InstagramParserRapidApiBundle\Command
 *
 * @since 25.02.2021
 */
class MakeFixtures extends Command
{
    /**
     * @var RetrieverInstagramDataInterface $parser Получение данных о картинках.
     */
    private $parser;

    /**
     * @var UserInfoRetriever $userIdRetriever Извлекатель информации о пользователе.
     */
    private $userIdRetriever;

    /**
     * @var string $username Код эккаунта.
     */
    private $username;

    /**
     * @var string $fixtureResponsePath Путь к фикстуре запроса картинок.
     */
    private $fixtureResponsePath;

    /**
     * @var string $fixtureUserPath Путь к фикстуре запроса данных пользователя.
     */
    private $fixtureUserPath;

    /**
     * @var OutputInterface $output
     */
    private $output;

    /**
     * MakeFixtures constructor.
     *
     * @param RetrieverInstagramDataInterface $parser              Получение данных о картинках.
     * @param UserInfoRetriever               $userIdRetriever     Извлекатель информации о пользователе.
     * @param string                          $username            Код эккаунта.
     * @param string                          $fixtureResponsePath Путь к фикстуре запроса картинок.
     * @param string                          $fixtureUserPath     Путь к фикстуре запроса данных пользователя.
     */
    public function __construct(
        RetrieverInstagramDataInterface $parser,
        UserInfoRetriever $userIdRetriever,
        string $username,
        string $fixtureResponsePath,
        string $fixtureUserPath
    ) {
        $this->parser = $parser;
        $this->userIdRetriever = $userIdRetriever;
        $this->username = $username;
        $this->fixtureResponsePath = $fixtureResponsePath;
        $this->fixtureUserPath = $fixtureUserPath;

        parent::__construct();
    }

    /**
     * Конфигурация.
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setName('make:instagram-fixtures')
            ->setDescription('Make Instagram fixtures')
            ->addArgument('username', InputArgument::OPTIONAL, 'Instagram user name')
        ;
    }

    /**
     * Исполнение команды.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $this->output = $output;

        $output->writeln('Начало процесса создания фикстур.');

        /** @var string $className Оригинальный класс. */
        $username = $input->getArgument('username') ?? $this->username;

        $userId = $this->makeUserFixture($username);
        if (!$userId) {
            return;
        }

        if (!$this->makeResponseFixture($userId)) {
            return;
        }

        $output->writeln('Фикстуры созданы успешно.');
    }

    /**
     * @param string $username Код эккаунта.
     *
     * @return string
     *
     * @throws InstagramTransportException | RuntimeException | InvalidArgumentException
     */
    private function makeUserFixture(string $username) : string
    {
        $this->userIdRetriever->setUseMock(false);
        $this->userIdRetriever->setUserName($username);

        $allData = $this->userIdRetriever->getAllData();

        if (!$this->write($this->fixtureUserPath, $allData)) {
            $this->output->writeln('Ошибка записи данных в фикстуру ' . $this->fixtureUserPath);
            return '';
        }

        $this->output->writeln('Фикстура данных пользователя создана.');

        if (!array_key_exists('id', $allData)) {
            throw new RuntimeException(
                'В ответе на данные пользователя отсутствует ключ ID.'
            );
        }

        return (string)$allData['id'];
    }

    /**
     * @param string $userId ID эккаунта.
     *
     * @return boolean
     *
     * @throws Exception
     */
    private function makeResponseFixture(string $userId) : bool
    {
        $this->parser->setUseMock(false);
        $this->parser->setUserId($userId);

        $allData = $this->parser->query();

        if (!$this->write($this->fixtureResponsePath, $allData)) {
            $this->output->writeln('Ошибка записи данных в фикстуру ' . $this->fixtureUserPath);
            return false;
        }

        $this->output->writeln('Фикстура ответа Инстаграма создана.');

        return true;
    }

    /**
     * Записать контент в файл.
     *
     * @param string $filename Путь к файлу.
     * @param array  $content  Контент.
     *
     * @return boolean
     */
    private function write(string $filename, array $content) : bool
    {
        return (bool)file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . $filename,
            json_encode($content)
        );
    }
}
