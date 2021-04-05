<?php

namespace Local\Services\Collections\Extenders;

use Illuminate\Support\Collection;

/**
 * Class Replace
 * @package Local\Services\Collections\Extenders
 * Perform a regular expression search and replace.
 *
 * @since 20.09.2020
 */
class Replace implements ExtenderCollectionInterface
{
    /**
     * @inheritDoc
     *
     */
    public function registerMacro(): void
    {
        if (Collection::hasMacro('replace')) {
            return;
        }

        Collection::macro('replace', function ($pattern, $replacement, $key = null) {
            return $this->transform(function ($item) use ($pattern, $replacement, $key) {
                if (!is_null($key)) {
                    $item->{$key} = preg_replace($pattern, $replacement, $item->{$key});
                } else {
                    $item = preg_replace($pattern, $replacement, $item);
                }

                return $item;
            });
        });
    }
}
