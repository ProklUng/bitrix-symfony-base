<?php

namespace Local\Services\Bitrix\WebForm;

use ArrayAccess;

/**
 * Class ObjectArItem
 * @package Local\Services\Bitrix\WebForm
 *
 * @see https://github.com/ASDAFF/hipot.framework/blob/master/lib/classes/Hipot/Utils/ObjectArItem.php
 */
class ObjectArItem implements ArrayAccess
{
    /**
     * Счетчик:
     * пустые записи на добавление [] индексируются как AUTOINDEX_NN
     * @var integer
     */
    private $cnt_append = 0;

    /**
     * Создание объекта из массива
     * @param array|null $result
     */
    public function __construct($result = null)
    {
        if (is_array($result)) {
            foreach ($result as $k => $v) {
                $this->offsetSet($k, $v);
            }
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->{$offset};
        } else {
            return null;
        }
    }

    public function offsetSet($offset, $value): void
    {
        if (trim($offset) == '') {
            $offset = 'AUTOINDEX_' . $this->cnt_append++;
        }
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->{$offset});
        }
    }
}