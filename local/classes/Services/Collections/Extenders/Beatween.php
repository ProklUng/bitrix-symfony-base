<?php

namespace Local\Services\Collections\Extenders;

use Illuminate\Support\Collection;

/**
 * Class Beatween
 * @package Local\Services\Collections\Extenders
 * Reduce each collection item to the value found between a given start and end string.
 *
 * @since 16.09.2020
 * @since 21.09.2020 Проверка на существование макроса.
 */
class Beatween implements ExtenderCollectionInterface
{
    /**
     * @inheritDoc
     *
     * @example collect(['"value1"', '"value2"', '"value3"',])->between('"', '"');
     *
     * Illuminate\Support\Collection { all: [ "value1", "value2", "value3", ] }
     */
    public function registerMacro(): void
    {
        if (Collection::hasMacro('between')) {
            return;
        }

        Collection::macro('between', function ($start, $end = null) {
            $end = $end ?? $start;
            /** @var Collection $obj */
            $obj = clone $this;

            return $obj->reduce(static function ($items, $value) use ($start, $end) {
                if (preg_match('/^' . $start . '(.*)' . $end . '$/', $value, $matches)) {
                    $items[] = $matches[1];
                }

                return collect($items);
            });
        });
    }
}
