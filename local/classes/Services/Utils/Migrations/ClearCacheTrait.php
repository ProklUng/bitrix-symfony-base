<?php

namespace Local\Services\Utils\Migrations;

/**
 * Class ClearCacheTrait
 * @package Local\Services\Utils\Migrations
 *
 * @since 11.04.2021
 */
class ClearCacheTrait
{
    /**
     * Очищает все виды кэша.
     *
     * @return void
     */
    protected function clearCache() : void
    {
        global $USER_FIELD_MANAGER;
        if ($USER_FIELD_MANAGER) {
            $USER_FIELD_MANAGER->CleanCache();
        }

        BXClearCache(true, '/');
    }
}
