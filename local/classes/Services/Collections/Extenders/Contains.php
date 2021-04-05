<?php

namespace Local\Services\Collections\Extenders;

use Illuminate\Support\Collection;

/**
 * Class Contains
 * @package Local\Services\Collections\Extenders
 * Contains macros.
 *
 * @since 20.09.2020
 */
class Contains implements ExtenderCollectionInterface
{
    private $macros = [
        'containsAll',
        'containsAll',
        'hasAll',
        'hasAny',
    ];
    /**
     * @inheritDoc
     *
     */
    public function registerMacro(): void
    {
        foreach ($this->macros as $macroName) {
            if (Collection::hasMacro($macroName)) {
                return;
            }
        }

        /**
         * This method returns true if the collection contains all elements of the given $subset.
         */
        Collection::macro('containsAll', function ($subset) {
            /** @var $this Collection */
            $data = $subset;
            if (!$subset instanceof Collection) {
                $data = Collection::make($subset);
            }

            return $data->filter(function ($value) {
                return !$this->contains($value);
            })->isEmpty();
        });

        /**
         * This method returns true if the collection contains any
         * of the elements given in $subset
         */
        Collection::macro('containsAny', function ($subset) {
            /** @var $this Collection */
            $data = $subset;
            if (!$subset instanceof Collection) {
                $data = Collection::make($subset);
            }

            return $data->filter(function ($value) {
                return $this->contains($value);
            })->isNotEmpty();
        });

        /**
         * This method checks if all the given keys in $subset are present in
         * the collection.
         */
        Collection::macro('hasAll', function ($subset) {
            /** @var $this Collection */
            $data = $subset;
            if (!$subset instanceof Collection) {
                $data = Collection::make($subset);
            }
            return $data->filter(function ($value) {
                return !$this->has($value);
            })->isEmpty();
        });

        /**
         * This method checks if any of the given keys in $subset exist in the collection.
         */
        Collection::macro('hasAny', function ($subset) {
            /** @var $this Collection */
            $data = $subset;
            if (!$subset instanceof Collection) {
                $data = Collection::make($subset);
            }
            return $data->filter(function ($value) {
                return $this->has($value);
            })->isNotEmpty();
        });
    }
}
