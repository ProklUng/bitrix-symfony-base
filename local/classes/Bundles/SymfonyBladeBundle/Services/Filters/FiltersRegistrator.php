<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\Filters;

/**
 * Class FiltersRegistrator
 * @package Local\Bundles\SymfonyBladeBundle\Services\Filters
 *
 * @since 09.03.2021
 */
class FiltersRegistrator
{
    /**
     * @var array $classes Классы фильтров.
     */
    private $classes = [];

    /**
     * FiltersRegistrator constructor.
     *
     * @param mixed ...$filters Сервисы, помеченные тэгом blade.custom.filter.
     */
    public function __construct(... $filters)
    {
        $result = [];
        foreach ($filters as $directive) {
            $iterator = $directive->getIterator();
            $result[] = iterator_to_array($iterator);
        }

        $result = array_merge([], ...$result);

        foreach ($result as $filter) {
            $this->classes[] = get_class($filter);
        }
    }

    /**
     * Cуществует ли такой фильтр (метод) в наборе фильтров?
     *
     * @param string $filter Фильтр (метод в классе).
     *
     * @return string
     */
    public function hasFilter(string $filter) : string
    {
        foreach ($this->classes as $filterClass) {
            if (method_exists($filterClass, $filter)) {
                return "\\" . $filterClass;
            }
        }

        return '';
    }
}
