<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Tools;

use ReflectionException;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomTestContainer
 * @package Tests\PhpUnitExtensions
 *
 * @since 18.11.2020
 */
class CustomTestContainer extends TestContainer
{
    /**
     * @var ContainerInterface $testContainer Контейнер.
     */
    private $testContainer;

    /**
     * @var ContainerInterface $backupOriginalContainer Бэкап контейнера.
     */
    private $backupOriginalContainer;

    /**
     * @param string $id
     * @param object|null $service
     *
     * @return void
     * @throws ReflectionException
     */
    public function set($id, $service) : void
    {
        $reflection = new ReflectionObject($this->testContainer);
        $property = $reflection->getProperty('services');
        $property->setAccessible(true);

        $services = $property->getValue($this->testContainer);

        $services[$id] = $service;

        $property->setValue($this->testContainer, $services);
    }

    /**
     * @param ContainerInterface $container Контейнер.
     *
     * @return void
     */
    public function setTestContainer(ContainerInterface $container) : void
    {
        $this->testContainer = $this->backupOriginalContainer = $container;
    }

    /**
     * Сбросить контейнер до первоначального состояния.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function reset() : void
    {
        $reflection = new ReflectionObject($this->testContainer);
        $property = $reflection->getProperty('services');
        $property->setAccessible(true);

        $property->setValue($this->testContainer, null);

        $this->testContainer = $this->backupOriginalContainer;
    }
}
