<?php

namespace Local\Services\Twig\Extensions;

use InvalidArgumentException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig_ExtensionInterface;
use Exception;
use Twig_SimpleFilter;

/**
 * Class SortByFieldExtension
 * @package Local\Services\Twig\Extensions
 *
 * @since 11.10.2020
 * @see https://github.com/victorhaggqvist/Twig-sort-by-field/blob/master/src/SortByFieldExtension.php
 */
class SortByFieldExtension extends AbstractExtension implements Twig_ExtensionInterface
{

    public function getName()
    {
        return 'sortbyfield';
    }

    /**
     * @return array|TwigFilter[]|Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('sortbyfield', [$this, 'sortByFieldFilter']),
        ];
    }

    /**
     * The "sortByField" filter sorts an array of entries (objects or arrays) by the specified field's value
     *
     * Usage: {% for entry in master.entries|sortbyfield('ordering', 'desc') %}
     *
     * @param mixed       $content
     * @param string|null $sort_by
     * @param string      $direction
     *
     * @return array
     * @throws Exception
     */
    public function sortByFieldFilter($content, string $sort_by = null, string $direction = 'asc')
    {
        if (!is_array($content)) {
            throw new InvalidArgumentException('Variable passed to the sortByField filter is not an array');
        } elseif (count($content) < 1) {
            return $content;
        } elseif ($sort_by === null) {
            throw new Exception('No sort by parameter passed to the sortByField filter');
        } elseif (!self::isSortable(current($content), $sort_by)) {
            throw new Exception('Entries passed to the sortByField filter do not have the field "' . $sort_by . '"');
        } else {
            // Unfortunately have to suppress warnings here due to __get function
            // causing usort to think that the array has been modified:
            // usort(): Array was modified by the user comparison function
            @usort($content, function ($a, $b) use ($sort_by, $direction) {
                $flip = ($direction === 'desc') ? -1 : 1;

                if (is_array($a)) {
                    $a_sort_value = $a[$sort_by];
                } else {
                    if (method_exists($a, 'get'.ucfirst($sort_by))) {
                        $a_sort_value = $a->{'get'.ucfirst($sort_by)}();
                    } else {
                        $a_sort_value = $a->$sort_by;
                    }
                }

                if (is_array($b)) {
                    $b_sort_value = $b[$sort_by];
                } else {
                    if (method_exists($b, 'get'.ucfirst($sort_by))) {
                        $b_sort_value = $b->{'get'.ucfirst($sort_by)}();
                    } else {
                        $b_sort_value = $b->$sort_by;
                    }
                }

                if ($a_sort_value == $b_sort_value) {
                    return 0;
                } else {
                    if ($a_sort_value > $b_sort_value) {
                        return (1 * $flip);
                    } else {
                        return (-1 * $flip);
                    }
                }
            });
        }

        return $content;
    }

    /**
     * Validate the passed $item to check if it can be sorted.
     *
     * @param mixed $item   Collection item to be sorted.
     * @param string $field Поле.
     *
     * @return boolean If collection item can be sorted
     */
    private static function isSortable($item, string $field) : bool
    {
        if (is_array($item)) {
            return array_key_exists($field, $item);
        } elseif (is_object($item)) {
            return isset($item->$field) || property_exists($item, $field);
        } else {
            return false;
        }
    }
}