<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Utils;

use Doctrine\Common\Annotations\Reader;
use Exception;
use Local\Bundles\BitrixDatabaseBundle\Services\Annotations\FieldParams;
use LogicException;
use ReflectionException;
use ReflectionMethod;

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
     * @var Reader $reader Читатель аннотаций.
     */
    private $reader;

    /**
     * @var array $fixtureDirectories Директории с фикстурами.
     */
    private $fixtureDirectories;

    /**
     * @var array $resolvedFixture Готовая фикстура.
     */
    private $resolvedFixture = [];

    /**
     * @var array $resolvedParams
     */
    private $resolvedParams = [];

    /**
     * FixtureResolver constructor.
     *
     * @param FixtureClassLocator $fixtureClassLocator Локатор классов с фикстурами.
     * @param Reader              $reader              Читатель аннотаций.
     * @param array               $fixtureDirectories  Директории с фикстурами.
     */
    public function __construct(
        FixtureClassLocator $fixtureClassLocator,
        Reader $reader,
        array $fixtureDirectories
    ) {
        $this->fixtureClassLocator = $fixtureClassLocator;
        $this->fixtureDirectories = $fixtureDirectories;
        $this->reader = $reader;
    }

    /**
     * Получить фикстуру.
     *
     * @param string $fixtureId ID фикстуры. content.common -> Тип инфоблока content.Код инфоблока common.
     *
     * @return array
     *
     * @throws ReflectionException
     * @internal Приоритеты: сначала пытаемся грузануть из файла. Затем из класса.
     */
    public function resolve(string $fixtureId) : array
    {
        $result = [];

        $fixtureFromFile = $this->loadFromFile($this->fixtureDirectories, $fixtureId);

        try {
            $fixtureFromClass = $this->fixtureClassLocator->locate($fixtureId);
            $classFixture = $this->fixtureClassLocator->getFixtureClass($fixtureId);
            $this->resolvedParams = $this->resolveParams($fixtureFromClass, $classFixture);
        } catch (LogicException $e) {
            $fixtureFromClass = [];
        }

        $result['PROPERTY_VALUES'] = array_merge((array)$fixtureFromFile['PROPERTY_VALUES'], (array)$fixtureFromClass['PROPERTY_VALUES']);

        $this->resolvedFixture = array_merge($result, $fixtureFromFile, $fixtureFromClass);

        return $this->resolvedFixture;
    }

    /**
     * Параметры из аннотации.
     *
     * @param array  $fixture      Фикстура.
     * @param string $fixtureClass Класс фикстуры.
     *
     * @return array
     * @throws ReflectionException
     */
    public function resolveParams(array $fixture, string $fixtureClass) : array
    {
        $result = [];

        try {
            /** @var FieldParams $annotation */
            $annotation = $this->reader->getMethodAnnotation(
                new ReflectionMethod($fixtureClass, 'fixture'),
                FieldParams::class
            );

            if ($annotation) {
                $result = $annotation->getParams();
            }
        } catch (Exception $e) {
            // Ошибки с аннотациями игнорирую.
            return [];
        }

        foreach ($fixture as $field => $item) {
            if ($field === 'PROPERTY_VALUES' && is_array($item)) {
                $result = $this->resolveParams($item, $fixtureClass);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getResolvedParams(): array
    {
        return $this->resolvedParams;
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