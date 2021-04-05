<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Service\Utils;

use ReflectionClass;
use ReflectionException;

/**
 * Class IgnoredAutowiringControllerParamsBag
 * @package Local\Bundles\CustomArgumentResolverBundle\Service\Utils
 *
 * @since 09.11.2020
 * @since 05.12.2020 Проверка входящих классов на существование.
 */
class IgnoredAutowiringControllerParamsBag
{
    /**
     * @var string[] $ignoredClasses Игнорируемые при автовайринге классы (учитывя наследование).
     * Например, DTO.
     */
    private static $ignoredClasses = [];

    /**
     * Добавить класс к числу игнорируемых.
     *
     * @param array $classes Классы.
     *
     * @return void
     */
    public function add(array $classes) : void
    {
        $classes = array_filter($classes, static function ($item) : bool {
            return class_exists($item) || interface_exists($item);
        });

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
        if (!class_exists($className)) {
            throw new ReflectionException(
                'Class ' . $className . ' not exist.'
            );
        }

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
