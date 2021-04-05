<?php

namespace Local\Services\Collections\Extenders;

use Illuminate\Support\Collection;

/**
 * Class TransformKeys
 * @package Local\Services\Collections\Extenders
 * Perform an operation on the collection's keys.
 *
 * @see https://github.com/sebastiaanluca/laravel-helpers#collection-macros (за основу)
 *
 * @since 16.09.2020
 */
class TransformKeys implements ExtenderCollectionInterface
{
    /**
     * @inheritDoc
     *
     */
    public function registerMacro(): void
    {
        Collection::macro('transformKeys', function (callable $operation) {
            /** @var Collection $obj */
            $obj = clone $this;

            return $obj->mapWithKeys(static function ($item, $key) use ($operation) {
                if (is_array($item)) {
                    $item = collect($item)->transformKeys($operation)
                                          ->toArray();
                }

                if ($item instanceof Collection) {
                    $item = $item->transformKeys($operation);
                }

                return [$operation($key) => $item];
            });
        });
    }
}
