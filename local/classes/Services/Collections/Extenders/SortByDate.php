<?php

namespace Local\Services\Collections\Extenders;

use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Class SortByDate
 * @package Local\Services\Collections\Extenders
 *
 * @internal Collection@sortByDate($key = null)
 *
 * Sort the values in a collection by a datetime value.
 *
 * To sort a simple list of dates, call the method without passing any arguments to it.
 * To sort a collection where the date is in a specific key, pass the key name when calling the method.
 *
 * collect(['2018-01-04', '1995-07-15', '2000-01-01'])->sortByDate();
 * // return collect(['1995-07-15', '2000-01-01', '2018-01-04'])
 *
 * collect([
 * ['date' => '2018-01-04', 'name' => 'Banana'],
 * ['date' => '1995-07-15', 'name' => 'Apple'],
 * ['date' => '2000-01-01', 'name' => 'Orange']
 * ])->sortByDate('date')
 *  ->all();
 *
 * // [
 * //    ['date' => '1995-07-15', 'name' => 'Apple'],
 * //    ['date' => '2000-01-01', 'name' => 'Orange'],
 * //    ['date' => '2018-01-04', 'name' => 'Banana']
 * // ]
 *
 * @since 20.09.2020
 */
class SortByDate implements ExtenderCollectionInterface
{
    /**
     * @inheritDoc
     *
     */
    public function registerMacro(): void
    {
        if (Collection::hasMacro('sortByDate')) {
            return;
        }

        Collection::macro('sortByDate', function ($key = null) {
            /** @var $this Collection */
            return $this->sortBy(function ($item) use ($key) {

                if (is_callable($key) && !is_string($key)) {
                    return $key($item);
                }

                $date = $key === null ? $item : $item[$key];

                if ($date instanceof Carbon) {
                    return $date->getTimestamp();
                }

                try {
                    return Carbon::parse($date)->getTimestamp();
                } catch (Exception $e) {
                }

                return 0;

            })->values();
        });
    }
}
