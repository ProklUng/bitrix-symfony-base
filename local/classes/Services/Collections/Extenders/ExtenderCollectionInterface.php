<?php

namespace Local\Services\Collections\Extenders;

/**
 * Interface ExtenderCollectionInterface
 * @package Local\Services\Collections\Extenders
 *
 * @since 16.09.2020
 */
interface ExtenderCollectionInterface
{
    /**
     * Регистрация нового макроса Сollection.
     *
     * @return void
     */
    public function registerMacro() : void;
}
