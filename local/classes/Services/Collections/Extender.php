<?php

namespace Local\Services\Collections;

use Local\Services\Collections\Extenders\ExtenderCollectionInterface;

/**
 * Class Extender
 * @package Local\Services\Collections
 *
 * @since 16.09.2020
 * @since 21.09.2020 Мелкая доработка.
 */
class Extender
{
    /**
     * @var ExtenderCollectionInterface[] $extenderCollection Экстендеры.
     */
    private $extenderCollection;

    /**
     * Extender constructor.
     *
     * @param ExtenderCollectionInterface ...$extenderCollection Экстендеры.
     */
    public function __construct(ExtenderCollectionInterface ...$extenderCollection)
    {
        $this->extenderCollection = $extenderCollection;
        $this->register();
    }

    /**
     * Регистрация экстендеров.
     *
     * @return void
     */
    protected function register() : void
    {
        if (empty($this->extenderCollection)) {
            return;
        }

        foreach ($this->extenderCollection as $extender) {
            $extender->registerMacro();
        }
    }
}
