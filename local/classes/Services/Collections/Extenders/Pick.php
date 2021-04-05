<?php

namespace Local\Services\Collections\Extenders;

use Illuminate\Support\Collection;

/**
 * Class Pick
 * @package Local\Services\Collections\Extenders
 * Расширение Collections: pluck по нескольким полям.
 *
 * @since 16.09.2020
 */
class Pick implements ExtenderCollectionInterface
{

    /**
     * @inheritDoc
     */
    public function registerMacro() : void
    {
        /**
         * Similar to pluck, with the exception that it can 'pluck' more than one column.
         * This method can be used on either Eloquent models or arrays.
         *
         * @param string|array $cols Set the columns to be selected.
         * @return Collection A new collection consisting of only the specified columns.
         */
        Collection::macro('pick', function ($cols = ['*']) {
            $cols = is_array($cols) ? $cols : func_get_args();
            /** @var Collection $obj */
            $obj = clone $this;

            // Just return the entire collection if the asterisk is found.
            if (in_array('*', $cols)) {
                return $this;
            }

            return $obj->transform(function ($value) use ($cols) {
                $ret = [];
                foreach ($cols as $col) {
                    // This will enable us to treat the column as a if it is a
                    // database query in order to rename our column.
                    $name = $col;
                    if (preg_match('/(.*) as (.*)/i', $col, $matches)) {
                        $col = $matches[1];
                        $name = $matches[2];
                    }

                    // If we use the asterisk then it will assign that as a key,
                    // but that is almost certainly **not** what the user
                    // intends to do.
                    $name = str_replace('.*.', '.', $name);

                    // We do it this way so that we can utilise the dot notation
                    // to set and get the data.
                    array_set($ret, $name, data_get($value, $col));
                }

                return $ret;
            });
        });
    }
}
