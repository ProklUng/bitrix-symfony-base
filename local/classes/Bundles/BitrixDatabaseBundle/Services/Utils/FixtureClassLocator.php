<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Utils;

use hanneskod\classtools\Iterator\ClassIterator;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureInterface;
use LogicException;
use Symfony\Component\Finder\Finder;

/**
 * Class FixtureClassLocator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Utils
 *
 * @since 11.04.2021
 */
class FixtureClassLocator
{
    /**
     * @var Finder $finder Поисковик Symfony.
     */
    private $finder;

    /**
     * @var array $fixtureDirectories Директории с фикстурами.
     */
    private $fixtureDirectories;

    /**
     * @var FixtureInterface[] $fixtures
     */
    private $fixtures;

    /**
     * FixtureClassLocator constructor.
     *
     * @param Finder $finder             Поисковик Symfony.
     * @param array  $fixtureDirectories Директории с фикстурами.
     */
    public function __construct(
        Finder $finder,
        array $fixtureDirectories
    ) {
        $this->fixtureDirectories = $fixtureDirectories;
        $this->finder = $finder;

        $this->fixtures = $this->getAllFixturesClasses();
    }

    /**
     * Поиск фикстуры.
     *
     * @param string $fixtureId ID фикстуры. content.common -> Тип инфоблока content.Код инфоблока common.
     *
     * @return array
     * @throws LogicException Фикстура не найдена.
     */
    public function locate(string $fixtureId) : array
    {
        foreach ($this->fixtures as $fixtureItem) {
            if ($fixtureItem->id() === $fixtureId) {
                return $fixtureItem->fixture();
            }
        }

        $this->throwError($fixtureId);
    }

    /**
     * Класс фикстуры.
     *
     * @param string $fixtureId ID фикстуры.
     *
     * @return string
     * @throws LogicException Фикстура не найдена.
     */
    public function getFixtureClass(string $fixtureId) : string
    {
        foreach ($this->fixtures as $fixtureItem) {
            if ($fixtureItem->id() === $fixtureId) {
                return get_class($fixtureItem);
            }
        }

        $this->throwError($fixtureId);
    }

    /**
     * Все классы фикстур по директориям из конфига.
     *
     * @return array
     */
    private function getAllFixturesClasses() : array
    {
        $result = [];

        foreach ($this->fixtureDirectories as $dir) {
            $iterator = new ClassIterator(
                $this->finder->in($_SERVER['DOCUMENT_ROOT'] . $dir)
            );

            foreach ($iterator->type(
                'Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureInterface'
            ) as $classname => $splFileInfo) {
                $result[] = new $classname;
            }
        }

        return $result;
    }

    /**
     * Выбросить ошибку.
     *
     * @param string $fixtureId ID фикстуры.
     *
     * @return void
     *
     * @throws LogicException
     */
    private function throwError(string $fixtureId) : void
    {
        throw new LogicException(
            'Фикстура по id ' . $fixtureId . ' не найдена.'
        );
    }
}