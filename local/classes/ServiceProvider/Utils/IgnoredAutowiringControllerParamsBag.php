<?php

namespace Local\ServiceProvider\Utils;

use ReflectionClass;
use ReflectionException;

/**
 * Class IgnoredAutowiringControllerParamsBag
 * @package Local\ServiceProvider\Utils
 *
 * @since 09.11.2020
 */
class IgnoredAutowiringControllerParamsBag
{
    /**
     * @var string[] $ignoredBaseClasses Игнорируемые при автовайринге классы (учитывя наследование).
     * Например, DTO.
     */
    private static $ignoredClasses = [];

    /**
     * Добавить игнорируемые классы.
     *
     * @param array $classes Классы.
     *
     * @return void
     */
    public function add(array $classes) : void
    {
        static::$ignoredClasses = array_merge(static::$ignoredClasses, $classes);
        static::$ignoredClasses = array_unique(static::$ignoredClasses);
    }

    /**
     * Находиться ли класс в исключении от автовязи?
     *
     * @param string $className Класс.
     *
     * @return boolean
     *
     * @throws ReflectionException Ошибки рефлексии.
     *
     * @since 08.11.2020
     */
    public function isIgnoredClass(string $className) : bool
    {
        $parentClasses = $this->getClassNames($className);

        $ignoredAutowiringClass = false;
        if ($parentClasses) {
            foreach ($parentClasses as $parentClass) {
                if (in_array($parentClass, static::$ignoredClasses, true)) {
                    $ignoredAutowiringClass = true;
                }
            }
        }

        return $ignoredAutowiringClass;
    }

    /**
     * Родительские классы.
     *
     * @param string $className Класс, подвергающийся обработке.
     *
     * @return array
     * @throws ReflectionException Ошибки рефлексии.
     *
     * @since 08.11.2020
     */
    protected function getClassNames(string $className) : array
    {
        $ref = new ReflectionClass($className);
        $parentRef = $ref->getParentClass();

        return array_unique(array_merge(
            [$className],
            $ref->getInterfaceNames(),
            $ref->getTraitNames(),
            $parentRef ?$this->getClassNames($parentRef->getName()) : []
        ));
    }
}
