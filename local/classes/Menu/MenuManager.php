<?php

namespace Local\Menu;

use Bitrix\Main\DB\Exception;
use CIBlock;
use CIBlockSection;
use CMain;
use CMenu;
use CModule;
use CSite;
use Local\Facades\IblockElementFacade;
use Local\Facades\IblockFacade;

/**
 * Class MenuManager
 * @package Local\Menu
 */
class MenuManager
{
    /** @var array $arMenuKeys Массив ключей для битриксового меню. */
    private static $arMenuKeys = [
        'NAME',
        'LINK',
        'ADDITIONAL_LINKS',
        'PARAMS',
        'CONDITION',
    ];

    /**
     * Генерирует меню на основе типов инфоблоков и директорий.
     *
     * @param array $arParams Параметры.
     *
     * @return array
     * @throws \Exception Системная ошибка.
     */
    public static function getMenuByIBlockType(array $arParams = ['IBLOCK_TYPE' => 'blocks'])
    {
        if (!CModule::IncludeModule('iblock')) {
            throw new \Exception('Module iblock not found!');
        }

        $cMain = new CMain();

        $isMainPage = CSite::InDir('/index.php');
        $currentPageUrl = $cMain->GetCurDir();

        $aMenuLinks = [];

        $rs = CIBlock::GetList([], ['TYPE' => $arParams['IBLOCK_TYPE']]);

        while ($arIBlock = $rs->Fetch()) {
            $iblockPageUrl = str_replace(
                ['#SITE_DIR#', '#IBLOCK_TYPE_ID#', '#IBLOCK_CODE#'],
                ['', $arIBlock['IBLOCK_TYPE_ID'], $arIBlock['CODE']],
                $arIBlock['LIST_PAGE_URL']
            );

            $iblockSelected = ((strpos($currentPageUrl, $iblockPageUrl) === 0) ||
                (strpos($iblockPageUrl, $currentPageUrl) === 0));

            //если на главной, то нет выделенных
            if ($isMainPage) {
                $iblockSelected = false;
            }

            $aMenuLinks[] = [
                $arIBlock['NAME'],
                $iblockPageUrl,
                [],
                [
                    'IBLOCK_ID' => $arIBlock['ID'],
                    'IS_PARENT' => true,
                    'DEPTH_LEVEL' => 1,
                    'SELECTED' => $iblockSelected,
                ],
                '',
            ];
            $rsSubMenu = CIBlockSection::GetList(
                ['SORT' => 'ASC'],
                ['IBLOCK_ID' => $arIBlock['ID'], 'ACTIVE' => 'Y']
            );

            while ($arSection = $rsSubMenu->fetch()) {
                $aMenuLinks[] = [
                    $arSection['NAME'],
                    str_replace(
                        ['#SITE_DIR#', '#IBLOCK_TYPE_ID#', '#IBLOCK_CODE#', '#SECTION_CODE#'],
                        ['', $arSection['IBLOCK_TYPE_ID'], $arIBlock['CODE'], $arSection['CODE']],
                        $arSection['SECTION_PAGE_URL']
                    ),
                    [],
                    ['IBLOCK_ID' => $arSection['ID'], 'IS_PARENT' => false, 'DEPTH_LEVEL' => 2],
                    '',
                ];
            }
        }

        return $aMenuLinks;
    }


    /**
     * Метод возвращает рекурсивный массив пунктов меню
     * @param string $sDir Директория, с которой начинать рекурсию.
     * @param string $sMenuType Тип меню.
     * @param boolean $bDisableRootLink Заменить ссылки корневого меню на
     * ссылку из первого дочернего элемента.
     * @param boolean $bUseExt Подключать файлы расширений.
     * @param integer $iMaxLevel Количество уровней для сканирования.
     * @param boolean $bCheckSelected Отмечать выбранные пункты.
     * @return array
     * @throws \Exception \Error.
     */
    public static function getTreeMenuByDir(
        string $sDir = '/',
        string $sMenuType = 'top',
        bool $bDisableRootLink = true,
        bool $bUseExt = true,
        int $iMaxLevel = 4,
        bool $bCheckSelected = true
    ): array {
        // Привести каталог в канонизированный абсолютный путь
        $sPath = realpath(
            strpos($_SERVER['DOCUMENT_ROOT'], $sDir) === false ? $_SERVER['DOCUMENT_ROOT'].$sDir : $sDir
        );
        // Проверяем корректный путь до дректории
        if (!is_dir($sPath)) {
            return [];
        } else {
            $arMenu = self::getRecursiveSubmenu(
                $sDir,
                $sMenuType,
                $bUseExt,
                $bCheckSelected,
                $iMaxLevel
            );
        }
        // Если есть дочерние элементы, то родительскому элементу
        // присваиваем ссылку из первого дочернего элемента
        if ($bDisableRootLink) {
            foreach ($arMenu as $iKey => $arItem) {
                if (!empty($arItem['CHILD'])) {
                    $arMenu[$iKey]['LINK'] = current($arItem['CHILD'])['LINK'];
                }
            }
        }

        return $arMenu;
    }

    /**
     * Метод рекурсивно обходит дочерние директории и формирует массив подменю.
     * @param string $sDir Директория, с которой начинать рекурсию.
     * @param string $sMenuType Тип меню.
     * @param boolean $bUseExt Подключать файлы расширений.
     * @param boolean $bCheckSelected Отмечать выбранные пункты.
     * @param integer $iMaxLevel Количество уровней для сканирования.
     * @param integer $iCurrentLevel Текущий уровень меню {@internal root = 0 }}.
     * @return array
     */
    private static function getRecursiveSubmenu(
        string $sDir = '/',
        string $sMenuType = 'top',
        bool $bUseExt = false,
        bool $bCheckSelected = true,
        int $iMaxLevel = 4,
        int $iCurrentLevel = 0
    ): array {
        $obMain = new CMain();
        /** @var string $sCurPage Текщая страница в браузере. */
        $sCurPage = $obMain->GetCurPage();

        $iCurrentLevel++;

        // Получаем текущий список меню.
        $menu = new CMenu($sMenuType);
        $menu->Init($sDir, $bUseExt, false, true);

        $menu->RecalcMenu($bUseExt, $bCheckSelected);
        $arMenus = $menu->arMenu;

        // Добавляем дочерние пункты меню рекурсивно.
        if (count($arMenus) > 0 && $iMaxLevel >= $iCurrentLevel) {
            foreach ($arMenus as $iKey => $arMenu) {
                // Присваеваем ключи массиву
                $arMenus[$iKey] = array_combine(self::$arMenuKeys, $arMenu);
                $arMenus[$iKey]['CHILD'] = self::getRecursiveSubmenu(
                    $arMenu[1],
                    $sMenuType,
                    $bUseExt,
                    $bCheckSelected,
                    $iMaxLevel,
                    $iCurrentLevel
                );

                $arMenus[$iKey]['DEPTH_LEVEL'] = $iCurrentLevel;

                // Указываем признак того, что элемент является родительским.
                $arMenus[$iKey]['PARAMS']['IS_PARENT'] = !empty($arMenus[$iKey]['CHILD']) && 0 < count(
                    $arMenus[$iKey]['CHILD']
                );

                // Указываем признак того, что элемент активен.
                $arMenus[$iKey]['PARAMS']['SELECTED'] = strpos($sCurPage, $arMenus[$iKey]['LINK']) !== false;
            }
        }

        return $arMenus;
    }

    /**
     * Метод получает один необходимый уровень меню из пути.
     * @param string $sDir Директория, с которой начинать рекурсию.
     * @param integer $iLvl Уровень, который необходимо получить.
     * @param string $sMenuType Тип меню.
     * @param boolean $bUseExt Подключать файлы расширений.
     *
     * @return array
     */
    public static function getOneLevelMenu(
        string $sDir = '/',
        int $iLvl = 1,
        string $sMenuType = 'top',
        bool $bUseExt = true
    ) {
        $arPath = explode('/', $sDir);

        return self::getRecursiveSubmenu($arPath[$iLvl], $sMenuType, $bUseExt, true, $iLvl);
    }

    /**
     * Строит меню по элементам инфоблока.
     * @param string $sIBlockType Тип инфоблока.
     * @param string $sIBlockCode Код инфоблока.
     *
     * @return array
     */
    public static function getMenuByIblockElements(string $sIBlockType, string $sIBlockCode): array
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $iIBId = IblockFacade::getIBlockIdByCodeCached($sIBlockType, $sIBlockCode);

        $arArgs = [
            'AR_ORDER' => ['SORT' => 'ASC'],
            'AR_FILTER' => [
                'IBLOCK_ID' => $iIBId,
                'ACTIVE' => 'Y',
            ],
            false,
            'AR_SELECT' => ['ID', 'NAME', 'DETAIL_PAGE_URL'],
        ];

        /** @noinspection PhpUndefinedMethodInspection */
        $arElements = IblockElementFacade::getIBlockElements($arArgs);

        $arMenuLinksByElement = [];

        foreach ($arElements as $arItem) {
            $arMenuLinksByElement[] = [
                $arItem['NAME'],
                $arItem['DETAIL_PAGE_URL'],
                [],
                [
                    'FROM_IBLOCK' => true,
                    'IS_PARENT' => false,
                    'DEPTH_LEVEL' => 1,
                ],
                '',
            ];
        }

        return $arMenuLinksByElement;
    }
}
