<?php

namespace Local\Util\Dictionaries;

use InvalidArgumentException;

/**
 * Class AbstractDictionary
 * @package Local\Util\Dictionaries
 */
abstract class AbstractDictionary
{
    /**
     * @return array
     */
    abstract public static function getItems(): array;

    /**
     * Возвращает элемент списка.
     *
     * @param  string $code Код.
     *
     * @return string
     */
    public static function getItem(string $code): string
    {
        if (!array_key_exists($code, static::getItems())) {
            throw new InvalidArgumentException('Запрошенный элемент ' . $code . ' отсутствует в словаре ' . __CLASS__);
        }

        return static::getItems()[$code];
    }

    public static function getKeyByValue(string $value)
    {
        return array_search($value, static::getItems());
    }

    /**
     * Возвращает коды существующих элементов словаря.
     *
     * @return array
     */
    public static function getCodes(): array
    {
        return array_keys(static::getItems());
    }

    /**
     * Возвращает массив значений словаря
     *
     * @return array
     */
    public static function getValues(): array
    {
        return array_values(static::getItems());
    }
}
