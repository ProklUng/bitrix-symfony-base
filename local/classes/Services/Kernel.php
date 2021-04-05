<?php

namespace Local\Services;

/**
 * Class Kernel
 * @package Local\Services
 */
class Kernel
{
    /**
     * Продакшен? Точнее DEBUG = false?
     *
     * @return boolean
     */
    public function isProduction() : bool
    {
        return env('DEBUG', false) === false;
    }
}
