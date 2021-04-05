<?php

namespace Local\Menu;

use CHTTP;
use Local\Util\Bitrix\Cacher;

/**
 * Class MenuCacher
 * Кэширование.
 * @package Local\Menu
 */
class MenuCacher extends Cacher
{
     /**
     * Salt кэша.
     *
     * @param array $arParams Параметры callback.
     *
     * @return string
     */
    protected function hashCache(array $arParams = []) : string
    {
        // Учесть 404, чтобы предотвратить замусоривание кэша.
        $process404 = (CHTTP::GetLastStatus() === '404 Not Found') ?
                      md5('404 Not Found')
                      :
                      md5($this->currentUrl);

        return md5(serialize($arParams)) . $process404;
    }
}
