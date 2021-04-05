<?php

namespace Local\Services;

use Bitrix\Main\ArgumentException;
use CFile;
use CIBlockElement;
use CIBlockSection;

/**
 * Class IBlockSectionManager
 * @package Local\Services
 */
class IBlockSectionManager
{
    /**
     * Имя раздела инфоблока или пустую строку.
     *
     * @param integer $iSectionID ИД раздела инфоблока.
     *
     * @return string
     */
    public function getSBlockSectionNameByID(int $iSectionID): string
    {
        $obBlockResult = CIBlockSection::GetByID($iSectionID);

        if ($arSection = $obBlockResult->GetNext()) {
            return $arSection['NAME'];
        }

        return '';
    }

    /** ID инфоблока.
     *
     * @param array $arParams Параметры с типом и кодом инфоблока.
     *
     * @return mixed
     * @throws ArgumentException
     */
    public static function getIBlockSectionIdByCode($arParams = ['IBLOCK_CODE', 'CODE', 'AR_SELECT'])
    {
        $res = CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_CODE' => $arParams['IBLOCK_CODE'],
                'CODE' => $arParams['CODE'],
            ],
            false,
            $arParams['AR_SELECT'],
            false
        );

        $arResult = $res->GetNext();
        if ($arResult['ID'] > 0) {
            //если есть картинка, взять путь к ней
            if ($arResult['PICTURE'] > 0) {
                $arResult['PICTURE'] = CFile::GetFileArray($arResult['PICTURE']);
            }

            return $arResult;
        }

        throw new ArgumentException(
            'Раздел в инфоблоке '.$arParams['IBLOCK_CODE'].' не найден',
            $arParams['CODE']
        );
    }

    /**
     * Элементы подраздела по ID.
     *
     * @param array $arParams
     *
     * @return array
     */
    public function getSectionsItemsByCode($arParams = ['IBLOCK_CODE', 'ID', 'AR_SORT', 'AR_SELECT'])
    {
        $res = CIBlockSection::GetList(
            [],
            [
                'IBLOCK_CODE' => $arParams['IBLOCK_CODE'],
                'ID' => $arParams['ID']
            ],
            false,
            [],
            false
        );

        if ($obRes = $res->GetNext()) {
            $iblockId = $obRes['IBLOCK_ID'];
            $idSection = $obRes['ID'];
        } else {
            return [];
        }

        $arSort = (!empty($arParams['AR_SORT'])) ? $arParams['AR_SORT'] : ['SORT' => 'ASC'];
        $arSelect = (!empty($arParams['AR_SELECT'])) ? $arParams['AR_SELECT'] : [];

        $res = CIBlockElement::GetList(
            $arSort,
            [
                'IBLOCK_ID' => $iblockId,
                'SECTION_ID' => $idSection,
                'ACTIVE' => 'Y'
            ],
            false,
            false,
            $arSelect
        );

        $arResult = [];

        while ($ob = $res->GetNext()) {
            $arResult[$ob['ID']] = $ob;
        }

        return $arResult;
    }
}
