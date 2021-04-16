<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators\Traits;

use Exception;

/**
 * Trait StringUtilsTrait
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators\Traits
 *
 * @since 11.04.2021
 */
trait StringUtilsTrait
{
    /**
     * Случайная строка (Фэйкер отказывается генерить строки меньше 5 символов длиной).
     *
     * @param integer $length Длина нужной строки.
     * @param string  $src    Альтернативный набор символов.
     *
     * @return string
     * @throws Exception
     */
    private function generateRandomString(int $length = 25, string $src = '') : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($src !== '') {
            $characters = $src;
        }

        $charactersLength = strlen($characters);

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
