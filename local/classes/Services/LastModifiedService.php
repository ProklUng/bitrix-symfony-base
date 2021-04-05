<?php

namespace Local\Services;

/**
 * Class LastModifiedService
 * @package Local\Services
 */
class LastModifiedService
{
    /** @var array $arLastModifiedData Дата последнего изменения последовательно по всей странице. */
    private static $arLastModifiedData = [];

    /**
     * Добавить дату.
     *
     * @param string $sHashCode Хэш-код.
     * @param mixed  $value     Таймстамп.
     *
     * @return void
     */
    public function add(string $sHashCode, $value): void
    {
        self::$arLastModifiedData[md5($sHashCode)] = $value;
    }

    /**
     * Накопленные данные.
     *
     * @return array
     */
    public function getData() : array
    {
        return self::$arLastModifiedData;
    }
    /**
     * Получить значение самого свежего изменения.
     *
     * @return mixed
     */
    public function getNewestModified()
    {
        if (empty(self::$arLastModifiedData)) {
            return '';
        }

        $arValues = array_values(self::$arLastModifiedData);

        // Сортировка по дате.
        usort($arValues, function ($a, $b) {
            if (strtotime($a) < strtotime($b)) {
                return 1;
            } else {
                if (strtotime($a) > strtotime($b)) {
                    return -1;
                } else {
                    return 0;
                }
            }
        });

        return $arValues[0];
    }
}
