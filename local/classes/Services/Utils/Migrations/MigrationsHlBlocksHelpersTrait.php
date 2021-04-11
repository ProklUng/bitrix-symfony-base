<?php

namespace Local\Services\Utils\Migrations;

use Bitrix\Highloadblock\HighloadBlockLangTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;

/**
 * Trait MigrationsHlBlocksHelpersTrait
 * @package Local\Services\Utils\Migrations
 *
 * @since 11.04.2021
 */
trait MigrationsHlBlocksHelpersTrait
{
    /**
     * @param string $hlblockName Название HL-блока.
     *
     * @return string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getEntityNameHlBlock(string $hlblockName) : string
    {
        $arData = $this->getHlblock($hlblockName);

        return 'HLBLOCK_' . $arData['ID'];
    }

    /**
     * @param string $hlblockName Название HL-блока.
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getHlblock(string $hlblockName) : array
    {
        $hlblock = HighloadBlockTable::getList(
            [
                'select' => ['*'],
                'filter' => [ 'NAME' => $hlblockName ],
            ]
        )->fetch();

        return $this->prepareHlblock($hlblock);
    }

    /**
     * @param array $item
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function prepareHlblock(array $item) : array
    {
        if (empty($item['ID'])) {
            return $item;
        }

        $langs = $this->getHblockLangs($item['ID']);
        if (!empty($langs)) {
            $item['LANG'] = $langs;
        }

        return $item;
    }

    /**
     * @param string $hlblockId
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getHblockLangs(string $hlblockId) : array
    {
        $result = [];

        if (!class_exists('\Bitrix\Highloadblock\HighloadBlockLangTable')) {
            return $result;
        }

        try {
            $dbres = HighloadBlockLangTable::getList([
                'filter' => ['ID' => $hlblockId],
            ]);

            while ($item = $dbres->fetch()) {
                $result[$item['LID']] = [
                    'NAME' => $item['NAME'],
                ];
            }
        } catch (Exception $e) {

        }

        return $result;
    }
}
