<?php

namespace Local\Services\Collections\Extenders;

use Illuminate\Support\Collection;

/**
 * Class SortByExternalKeys
 * @package Local\Services\Collections\Extenders
 * Сортировка коллекции по массиву снаружи переданных ключей.
 *
 * @since 17.09.2020
 */
class SortByExternalKeys implements ExtenderCollectionInterface
{
    /**
     * @inheritDoc
     *
     */
    public function registerMacro(): void
    {
        Collection::macro('sortByExternalKeys', function ($arrayKeys) {
            if (!is_array($arrayKeys)) {
                $arrayKeys = (array)$arrayKeys;
            }

            $arResult = [];
            foreach ($arrayKeys as $newKey) {
                $arResult[] =  $this[$newKey] ?? null;
            }

            return collect($arResult)->filter();
        });
    }
}
