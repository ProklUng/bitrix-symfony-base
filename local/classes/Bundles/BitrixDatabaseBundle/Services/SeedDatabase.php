<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\Entity\DataManager;
use Exception;
use LogicException;
use Module\CustomTable\News\NewsTable;
use RuntimeException;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class SeedDatabase
 * @package Local\Bundles\BitrixDatabaseBundle\Services
 *
 * @since 08.04.2021
 */
class SeedDatabase
{
    /**
     * @var ServiceLocator $entityLocator Локатор с сущносятми таблиц.
     */
    private $entityLocator;

    /**
     * @var DataManager $entity
     */
    private $entity;

    /**
     * @var string $table Название таблицы.
     */
    private $table = '';

    /**
     * @var string $prefix Префикс таблиц.
     */
    private $prefix = '';

    /**
     * SeedDatabase constructor.
     *
     * @param ServiceLocator $entityLocator Локатор сущностей.
     */
    public function __construct(
        ServiceLocator $entityLocator
    )
    {
        $this->entityLocator = $entityLocator;
    }

    /**
     * Вставить из фикстуры.
     *
     * @param array $fixture Фикстура.
     *
     * @return void
     * @throws LogicException | RuntimeException | Exception
     */
    public function fromFixture(array $fixture) : void
    {
        if (count($fixture) === 0) {
            return;
        }

        foreach ($fixture as $item) {
            $this->insert($item);
        }
    }

    /**
     * Вставить запись.
     *
     * @param array $data Данные.
     *
     * @return void
     * @throws LogicException | RuntimeException | Exception
     */
    public function insert(array $data) : void
    {
        if ($this->table === '') {
            throw new LogicException('Таблица не задана.');
        }

        $result = $this->entity::add($data);

        if (!$result->isSuccess()) {
            $errorCollection = $result->getErrorMessages();
            throw new RuntimeException(
                'Вставка данных не задалась: ' . implode(' ', $errorCollection)
            );
        }
    }

    /**
     * Очистить таблицу.
     *
     * @return void
     * @throws SqlQueryException
     */
    public function truncate() : void
    {
        if ($this->table === '') {
            throw new LogicException(
                'Таблица не задана.'
            );
        }

        $connection = Application::getConnection();
        $connection->truncateTable($this->entity::getTableName());
    }

    /**
     * @param string $table Название таблицы.
     *
     * @return SeedDatabase
     */
    public function setTable(string $table): SeedDatabase
    {
        $this->table = $this->prefix ? $this->prefix . $table : $table;
        $this->entity = $this->locateEntityData($this->table);

        return $this;
    }

    /**
     * @param string $prefix Префикс таблиц.
     *
     * @return SeedDatabase
     */
    public function setPrefix(string $prefix): SeedDatabase
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @param string $table Таблица.
     *
     * @return DataManager
     * @throws LogicException
     */
    private function locateEntityData(string $table) : DataManager
    {
        foreach ($this->entityLocator->getProvidedServices() as $serviceId => $value) {
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
