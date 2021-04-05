<?php

namespace Local\Services\Collections\Extenders;

use Illuminate\Support\Collection;

/**
 * Class RemovePropertySuffix
 * @package Local\Services\Collections\Extenders
 * Удалить суффикс PROPERTY_*_VALUE.
 *
 * @since 17.09.2020
 */
class RemovePropertySuffix implements ExtenderCollectionInterface
{
    /**
     * @inheritDoc
     *
     */
    public function registerMacro(): void
    {
        Collection::macro('removePropertySuffix', function () {

            /* @var $this Collection */
            return $this->transformKeys([RemovePropertySuffix::class, 'callback']);
        });
    }

    public function callBack($key)
    {
        $result = preg_match('/^PROPERTY_(.*)_VALUE'.'$/', $key, $matches);
        if (!$result) {
            return $key;
        }

        return $matches[1];
    }
}
