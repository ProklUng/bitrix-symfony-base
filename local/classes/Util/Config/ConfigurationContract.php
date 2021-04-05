<?php

namespace Local\Util\Config;

/**
 * Interface ConfigurationContract
 * @package Local\Util\Config
 */
interface ConfigurationContract
{
    /**
     * Get config value.
     *
     * @param string $key Ключ.
     *
     * @return mixed
     */
    public function get(string $key);
}
