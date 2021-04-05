<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\Filters;

use Exception;

/**
 * Class BladeFiltersCompiler
 * @package Local\Bundles\SymfonyBladeBundle\Services\Filters
 *
 * @since 09.03.2021
 */
class BladeFiltersCompiler
{
    /**
     * @var FiltersRegistrator $filterFinder Поиск фильтров.
     */
    private $filterFinder;

    /**
     * BladeFiltersCompiler constructor.
     * @throws Exception
     */
    public function __construct()
    {
        // Из-за сопряжения с Laravel Container пусть остается сервис-локатор.
        // Хоть это и плохо.
        /** @psalm-suppress PropertyTypeCoercion */
        $this->filterFinder = container()->get(FiltersRegistrator::class);
    }

    /**
     * Compile the echo statements.
     *
     * @param string $value Значение.
     *
     * @return string
     */
    public function compile(string $value) : string
    {
        return preg_replace_callback('/(?<=((?<!@){{))(.*?)(?=}})/mu',
            function (array $matches) : string {
                return $this->parseFilters($matches[0]);
            }, $value
        );
    }

    /**
     * Parse the blade filters.
     *
     * @param string $value Значение.
     *
     * @return string
     */
    protected function parseFilters(string $value) : string
    {
        if (!preg_match('/(?=(?:[^\'\"\`)]*([\'\"\`])[^\'\"\`]*\1)*[^\'\"\`)]*$)(\|.*)/u', $value, $matches)) {
            return $value;
        }

        $filters = preg_split('/\|(?=(?:[^\'\"\`]*([\'\"\`])[^\'\"\`]*\1)*[^\'\"\`]*$)/u', $matches[0]);

        if (empty($filters = array_values(array_filter(array_map('trim', $filters))))) {
            return $value;
        }

        $wrapped = '';

        foreach ($filters as $key => $filter) {
            $filter = preg_split('/:(?=(?:[^\'\"\`]*([\'\"\`])[^\'\"\`]*\1)*[^\'\"\`]*$)/u', trim($filter));

            $filterClass = $this->filterFinder->hasFilter($filter[0]);
            if ($filterClass !== '') {
                $wrapped = sprintf(
                    $filterClass . '::%s(%s%s)',
                    $filter[0],
                    $key === 0 ? trim(str_replace($matches[0], '', $value)) : $wrapped,
                    isset($filter[1]) ? ",{$filter[1]}" : ''
                );
            }
        }

        return $wrapped;
    }
}
