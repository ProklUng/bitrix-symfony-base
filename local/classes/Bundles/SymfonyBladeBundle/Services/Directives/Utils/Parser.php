<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\Directives\Utils;

use Illuminate\Support\Collection;

/**
 * Class Parser
 * @package Local\Bundles\SymfonyBladeBundle\Services\Directives\Utils
 *
 * @since 09.03.2021
 */
class Parser
{
    /**
     * Parse expression.
     *
     * @param string $expression Выражение.
     *
     * @return Collection
     */
    public static function multipleArgs(string $expression) : Collection
    {
        return collect(explode(',', $expression))->map(function ($item) : string {
            return trim($item);
        });
    }

    /**
     * Strip quotes.
     *
     * @param string $expression Выражение.
     *
     * @return string
     */
    public static function stripQuotes(string $expression) : string
    {
        return str_replace(["'", '"'], '', $expression);
    }
}
