<?php

if (!function_exists('bxConfig')) {
    function bxConfig(string $key)
    {
        return container()->get('bx.config')->get($key);
    }
}

if (!function_exists('bxConfig')) {
    /**
     * Запускаем из под phpunit?
     *
     * @return boolean
     *
     */
    function isPHPUnit(): bool
    {
        // defined by PHPUnit
        return defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__');
    }
}
