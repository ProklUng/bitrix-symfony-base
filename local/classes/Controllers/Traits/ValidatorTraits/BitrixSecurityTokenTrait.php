<?php

namespace Local\Controllers\Traits\ValidatorTraits;

/**
 * Trait BitrixSecurityTokenTrait
 * Трэйт, указывающий проверять токен безопасности Битрикс.
 * @package Local\Controllers\Traits\ValidatorTraits
 *
 * @since 10.09.2020
 */
trait BitrixSecurityTokenTrait
{
    /** @var boolean $disableCheckToken Проверять ли токен или нет. */
    private static $disableCheckToken = true;

    /**
     * Boot trait.
     *
     * @return void
     */
    public static function bootBitrixSecurityTokenTrait() : void
    {
        // Для окружения dev игнорировать проверку.
        self::$disableCheckToken = !getenv('DEBUG', false);
    }

    /**
     * Геттер - нужно ли проверять токен или нет.
     *
     * @return boolean
     */
    public function needCheckToken() : bool
    {
        return self::$disableCheckToken;
    }
}
