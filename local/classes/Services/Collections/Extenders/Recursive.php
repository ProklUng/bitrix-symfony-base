<?php

namespace Local\Services\Collections\Extenders;

use Illuminate\Support\Collection;

/**
 * Class Recursive
 * @package Local\Services\Collections\Extenders
 * Recursively convert nested arrays into Laravel Collections.
 *
 * @since 18.09.2020
 * @since 20.09.2020 Проверка на существование макроса.
 */
class Recursive implements ExtenderCollectionInterface
{
    /**
     * @inheritDoc
     *
     */
    public function registerMacro(): void
    {
        if (Collection::hasMacro('recursive')) {
            return;
        }

        Collection::macro('recursive', function () {
            /** @var $this Collection */
            return $this->map(static function ($value) {
                if (is_array($value) || is_object($value)) {
                    return (collect($value))->recursive();
                }

                return $value;
            });
        });
    }
}
