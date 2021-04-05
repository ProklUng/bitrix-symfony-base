<?php

namespace Local\Services\Console;

use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * Class ConsoleCommandConfiguratorSimple
 * @package Local\Services\Console
 *
 * @since 10.12.2020
 */
class ConsoleCommandConfiguratorSimple
{
    /**
     * @var Application $application Конфигуратор консольных команд.
     */
    private $application;

    /**
     * @var Command[] $commands Команды.
     */
    private $commands;

    /**
     * ConsoleCommandConfigurator constructor.
     *
     * @param Application $application Конфигуратор консольных команд.
     * @param Command     ...$commands Команды.
     */
    public function __construct(
        Application $application,
        Command ...$commands
    ) {
        $this->application = $application;
        $this->commands = $commands;
    }

    /**
     * Инициализация команд.
     *
     * @return $this
     */
    public function init() : self
    {
        foreach ($this->commands as $command) {
            $this->application->add($command);
        }

        return $this;
    }

    /**
     * Запуск команд.
     *
     * @throws Exception
     */
    public function run() : void
    {
        $this->application->run();
    }

    /**
     * Добавить команды.
     *
     * @param array $commands Команды
     *
     * @return void
     */
    public function add(...$commands) : void
    {
        foreach ($commands as $command) {
            $iterator = $command->getIterator();
            $array = iterator_to_array($iterator);
            $this->commands = array_merge($this->commands, $array);
        }
    }
}
