<?php

namespace Local\Util\Bitrix;

use CIBlockElement;
use CIBlockSection;
use Exception;

/**
 * Class IblockTree
 * Генерация дерева из инфоблока, включающее
 * элементы.
 * @package Local\Util\Bitrix
 */
class IblockTree
{
    /** @var integer $iblockId ID инфоблока. */
    protected $iblockId;
    /** @var array $arTree Результирующее дерево. */
    protected $arTree = [];
    /** @var array $arLanguageFile Языковый файл. */
    protected $arLanguageFile = [];

    /**
     * IblockTree constructor.
     *
     * @param integer $iblockId ID инфоблока.
     */
    public function __construct(int $iblockId)
    {
        $this->iblockId = $iblockId;
    }

    /**
     * Собрать дерево, включающее элементы.
     *
     * @return array
     */
    public function get(): array
    {
        try {
            $this->makeTree($this->iblockId);

            return $this->arTree;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Передать языковый файл.
     *
     * @param array $arLanguageFile Языковый файл.
     *
     * @return IblockTree
     */
    public function setLanguageFile(array $arLanguageFile): IblockTree
    {
        $this->arLanguageFile = $arLanguageFile;

        return $this;
    }

    /**
     * Получить данные об элементах меню.
     *
     * @param integer $iblockId   ID инфоблока.
     * @param integer $iSectionId ID секции инфоблока.
     * @param array   $arLang     Языковый файл (массив $MESS).
     *
     * @return array
     */
    protected function getItems(int $iblockId, int $iSectionId, array $arLang = []): array
    {
        $arItems = [];

        $res = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'IBLOCK_SECTION_ID' => $iSectionId, 'ACTIVE' => 'Y'],
            false,
            false,
            ['NAME', 'PROPERTY_VOLUME', 'PROPERTY_PRICE', 'IBLOCK_SECTION_ID', 'PROPERTY_WEIGHT', 'PREVIEW_TEXT']
        );

        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();

            $arItems[] = [
                'NAME' => $arFields['NAME'],
                'PREVIEW_TEXT' => $arFields['PREVIEW_TEXT'],
                'PRICE' => $arFields['PROPERTY_PRICE_VALUE'].' '.$arLang['RUB'], // Цена.
                'VOLUME' => $arFields['PROPERTY_VOLUME_VALUE'].' '.$arLang['VOLUME'], // Объем.
                'WEIGHT' => $arFields['PROPERTY_WEIGHT_VALUE'].' '.$arLang['VOLUME'] // Вес
            ];
        }

        return $arItems;
    }

    /**
     * @param integer $iblockId ID инфоблока.
     *
     * @throws Exception $e Неправильный код инфоблока.
     * @return void
     */
    protected function makeTree(int $iblockId) : void
    {
        /**
         * Построение дерева.
         * @internal См. https://yunaliev.ru/2014/01/razdely-infobloka-v-vide-massiva-1s-bitriks/
         */

        if (!$this->iblockId) {
            throw new Exception('Make tree: invalid ID infoblock');
        }

        $obSection = CIBlockSection::GetList(
            ['DEPTH_LEVEL' => 'desc', 'SORT' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
            false,
            ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'SORT', 'IBLOCK_ID']
        );

        /** Результат сборки данных в дерево. */
        $arSectionList = [];
        /** Уровни вложенности. */
        $arDepthLevel = [];

        while ($arSection = $obSection->GetNext(true, false)) {
            $arPush = $arSection;

            // Подмес элементов (если они существуют)
            $arItems = $this->getItems($iblockId, $arSection['ID'], $this->arLanguageFile);
            if (!empty($arItems)) {
                $arPush['ITEMS'] = $arItems;
            }

            $arSectionList[$arSection['ID']] = $arPush;
            $arDepthLevel[] = $arSection['DEPTH_LEVEL'];
        }

        $ar_DepthLavelResult = array_unique($arDepthLevel);
        rsort($ar_DepthLavelResult);

        $i_MaxDepthLevel = (int)$ar_DepthLavelResult[0];

        for ($i = $i_MaxDepthLevel; $i > 1; $i--) {
            foreach ($arSectionList as $iSectionID => $arValue) {
                if ($arValue['DEPTH_LEVEL'] == $i) {
                    $arSectionList[$arValue['IBLOCK_SECTION_ID']]['SUB_SECTION'][] = $arValue;
                    unset($arSectionList[$iSectionID]);
                }
            }
        }

        // Финальная сортировка дерева по индексу SORT.
        usort(
            $arSectionList,
            function ($a, $b) {
                if ($a['SORT'] == $b['SORT']) {
                    return 0;
                }

                return ($a['SORT'] < $b['SORT']) ? -1 : 1;
            }
        );
        $this->arTree = $arSectionList; // Результат.
    }
}
