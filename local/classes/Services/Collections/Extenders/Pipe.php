<?php

namespace Local\Services\Collections\Extenders;

use Illuminate\Support\Collection;

/**
 * Class KSort
 * @package Local\Services\Collections\Extenders
 * Pass collection to an anonymous function.
 *
 * @since 20.09.2020
 */
class Pipe implements ExtenderCollectionInterface
{
    /**
     * @inheritDoc
     *
     */
    public function registerMacro(): void
    {
        if (Collection::hasMacro('pipe')) {
            return;
        }

        /**
         * Pass collection to an anonymous function.
         * Inspired by https://murze.be/2016/05/getting-package-statistics-packagist-redux/
         *
         * @param  callable $callback
         *
         * @return mixed
         */
        Collection::macro('pipe', function ($callback) {
            return $callback($this);
        });
    }
}
