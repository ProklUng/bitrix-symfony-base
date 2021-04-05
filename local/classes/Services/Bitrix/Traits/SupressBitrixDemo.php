<?php

namespace Local\Services\Bitrix\Traits;

/**
 * Trait SupressBitrixDemo
 * Подавить вывод сообщения демо-версии при вызове через роутер.
 * @package Local\Services\Bitrix\Traits
 *
 * @since 11.10.2020
 */
trait SupressBitrixDemo
{
    /**
     * Подавить вывод сообщения демо-версии при вызове через роутер.
     * @return void
     */
    public function initializeSupressBitrixDemo() : void
    {
        // Убираем надпись о просрочке сайта.
        global $SiteExpireDate;
        $SiteExpireDate = $SiteExpireDate * 10;
    }
}