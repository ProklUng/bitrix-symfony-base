<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Utils;

use LogicException;

/**
 * Class FixtureResolver
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Utils
 *
 * @since 11.04.2021
 */
class FixtureResolver
{
    /**
     * @var FixtureClassLocator $fixtureClassLocator Локатор классов с фикстурами.
     */
    private $fixtureClassLocator;

    /**
     * @var array $fixtureDirectories Директории с фикстурами.
     */
    private $fixtureDirectories;

    /**
     * FixtureResolver constructor.
     *
     * @param FixtureClassLocator $fixtureClassLocator Локатор классов с фикстурами.
     * @param array               $fixtureDirectories  Директории с фикстурами.
     */
    public function __construct(
        FixtureClassLocator $fixtureClassLocator,
        array $fixtureDirectories
    ) {
        $this->fixtureClassLocator = $fixtureClassLocator;
        $this->fixtureDirectories = $fixtureDirectories;
    }

    /**
     * Получить фикстуру.
     *
     * @param string $fixtureId ID фикстуры. content.common -> Тип инфоблока content.Код инфоблока common.
     *
     * @return array
     *
     * @internal Приоритеты: сначала пытаемся грузануть из файла. Затем из класса.
     */
    public function resolve(string $fixtureId) : array
    {
        $result = [];

        $fixtureFromFile = $this->loadFromFile($this->fixtureDirectories, $fixtureId);

        try {
            $fixtureFromClass = $this->fixtureClassLocator->locate($fixtureId);
        } catch (LogicException $e) {
            $fixtureFromClass = [];
        }

        $result['PROPERTY_VALUES'] = array_merge((array)$fixtureFromFile['PROPERTY_VALUES'], (array)$fixtureFromClass['PROPERTY_VALUES']);

        return array_merge($result, $fixtureFromFile, $fixtureFromClass);
    }

    /**
     * Загрузить фикстуру из файла.
     *
     * @param array  $fixturePaths Пути к фикстурам.
     * @param string $fileName     Название таблицы.
     *
     * @return array
     */
    private function loadFromFile(array $fixturePaths, string $fileName) : array
    {
        foreach ($fixturePaths as $path) {
            $pathFile = $_SERVER['DOCUMENT_ROOT'] . $path . $fileName . '.php';

            if (!@file_exists($pathFile)) {
                continue;
            }

            $result = include $pathFile;
            if (is_array($result)) {
                return $result;
            }
        }

        return [];
    }
}