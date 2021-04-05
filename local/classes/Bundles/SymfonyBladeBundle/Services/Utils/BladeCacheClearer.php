<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\Utils;

use Bitrix\Main\Application;
use Local\Bundles\SymfonyBladeBundle\Services\Providers\BladeProvider;

/**
 * Class BladeCacheClearer
 * @package Local\Bundles\SymfonyBladeBundle\Services\Utils
 *
 * @since 09.03.2021
 */
class BladeCacheClearer
{
    /**
     * Движуха.
     *
     * @return void
     */
    public function clear() : void
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $clearCache = htmlspecialchars($request->getQuery('clear_cache'));

        if ($clearCache === 'Y') {
            try {
                BladeProvider::clearCache();
            } catch (\Exception $e) {}
        }
    }
}
