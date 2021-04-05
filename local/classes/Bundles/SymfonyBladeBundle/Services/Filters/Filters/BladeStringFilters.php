<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\Filters\Filters;

/**
 * Class BladeStringFilters
 * @package Local\Bundles\SymfonyBladeBundle\Services\Filters\Filters
 *
 * @since 09.03.2021
 */
class BladeStringFilters
{
    /**
     * Transform the first letter to lowercase.
     *
     * @param string $value Значение.
     *
     * @return string
     */
    public static function lcfirst(string $value): string
    {
        return mb_strtolower(mb_substr($value, 0, 1)).mb_substr($value, 1);
    }

    /**
     * Transform the first letter to uppercase.
     *
     * @param string $value Значение.
     *
     * @return string
     */
    public static function ucfirst(string $value) : string
    {
        return mb_strtoupper(mb_substr($value, 0, 1)).mb_substr($value, 1);
    }

    /**
     * Format the string as date.
     *
     * @param string $value  Значение.
     * @param string $format Формат даты.
     *
     * @return string
     */
    public static function date(string $value, string $format = 'Y-m-d') : string
    {
        return date($format, strtotime($value));
    }

    /**
     * Trim the string.
     *
     * @param string $value Значение.
     *
     * @return string
     */
    public static function trim(string $value) : string
    {
        return trim($value);
    }
}
