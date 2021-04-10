<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Traits;

/**
 * Trait DataGeneratorTrait
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Traits
 *
 * @since 10.04.2021
 */
trait DataGeneratorTrait
{
    /**
     * Загрузить фикстуру из файла.
     *
     * @param array  $fixturePaths Пути к фикстурам.
     * @param string $fileName     Название таблицы.
     *
     * @return array
     */
    private function loadFixtureFromFile(array $fixturePaths, string $fileName) : array
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