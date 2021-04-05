<?php

namespace Local\Services\Console;

use Exception;
use IteratorAggregate;
use Local\ServiceProvider\Bundles\BundlesLoader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ConsoleCommandConfigurator
 * @package Local\Services\Console
 *
 * @since 10.12.2020
 * @since 20.12.2020 Рефакторинг. Форк нативного способа подключения команд.
 * @since 26.02.2021 Убрал array_merge в цикле.
 */
class ConsoleCommandConfigurator
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
     * @var ContainerInterface $servicesIdCommands
     */
    private $container;

    /**
     * ConsoleCommandConfigurator constructor.
     *
     * @param Application        $application Конфигуратор консольных команд.
     * @param ContainerInterface $container   Контейнер.
     */
    public function __construct(
        Application $application,
        ContainerInterface $container
    ) {
        $this->application = $application;
        $this->container = $container;
    }

    /**
     * Инициализация команд.
     *
     * @return self
     */
    public function init(): self
    {
        $this->registerCommands();

        return $this;
    }

    /**
     * Запуск команд.
     *
     * @return void
     * @throws Exception
     */
    public function run(): void
    {
        $this->application->run();
    }

    /**
     * Добавить команды.
     *
     * @param array $commands Команды.
     *
     * @return void
     * @throws Exception
     */
    public function add(...$commands): void
    {
        $result = [];

        foreach ($commands as $command) {
            $array = $command;
            if ($command instanceof IteratorAggregate) {
                $iterator = $command->getIterator();
                $array = iterator_to_array($iterator);
            }

            $result[] = $array;
        }

        $this->commands = array_merge($this->commands, $result);
    }

    /**
     * Finds a command by name or alias.
     *
     * Contrary to get, this command tries to find the best
     * match if you give it an abbreviation of a name or alias.
     *
     * @param string $name A command name or a command alias.
     *
     * @return Command A Command instance
     *
     * @throws CommandNotFoundException When command name is incorrect or ambiguous
     */
    public function find(string $name) : Command
    {
        return $this->application->find($name);
    }

    /**
     * Регистрация команд.
     *
     * @return void
     */
    private function registerCommands() : void
    {
        $bundles = BundlesLoader::getBundlesMap();

        foreach ($bundles as $bundle) {
            if ($bundle instanceof Bundle) {
                $bundle->registerCommands($this->application);
            }
        }

        if ($this->container->has('console.command_loader')) {
            $this->application->setCommandLoader(
                $this->container->get('console.command_loader')
            );
        }

        if ($this->container->hasParameter('console.command.ids')) {
            $lazyCommandIds = $this->container->hasParameter('console.lazy_command.ids')
                ? $this->container->getParameter('console.lazy_command.ids') :
                [];

            foreach ($this->container->getParameter('console.command.ids') as $id) {
                if (!isset($lazyCommandIds[$id])) {
                    $this->application->add(
                        $this->container->get($id)
                    );
                }
            }
        }
    }
}
