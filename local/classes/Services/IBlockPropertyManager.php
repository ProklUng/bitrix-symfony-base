<?php

namespace Local\Services;

use CIBlockProperty;
use CIBlockPropertyEnum;
use Local\Constants;
use Local\Facades\CacherFacade;

/**
 * Class IBlockPropertyManager
 * @package Local\Services
 */
class IBlockPropertyManager
{
    /** Свойство типа - список с XML_ID.
     *
     * @param array $arParams Параметры с типом и кодом инфоблока.
     *
     * @return array
     */
    public function getPropertyEnumListByCode($arParams = ['IBLOCK_ID', 'PROPERTY_CODE']) : array
    {
        $arPropEnumList = [];

        $arSort = ['ID' => 'ASC'];
        $arFilter = ['IBLOCK_ID' => $arParams['IBLOCK_ID'], 'CODE' => $arParams['PROPERTY_CODE']];

        $rs = CIBlockPropertyEnum::GetList($arSort, $arFilter);

        while ($ob = $rs->Fetch()) {
            $arPropEnumList[$ob['ID']] = $ob;
        }

        return $arPropEnumList;
    }

    /**
     * Свойство типа - список с XML_ID из кэша.
     *
     * @param string $iblockID      ID инфоблока.
     * @param string $sPropertyCode Код свойства инфоблока.
     *
     * @return mixed
     */
    public function getPropertyEnumListByCodeCached($iblockID, $sPropertyCode)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return CacherFacade::setCacheId('property' . $iblockID . $sPropertyCode)
            ->setCallback([$this, 'getPropertyEnumListByCode'])
            ->setCallbackParams(['IBLOCK_ID' => $iblockID, 'PROPERTY_CODE' => $sPropertyCode])
            ->setTtl(Constants::SECONDS_IN_WEEK)
            ->returnResultCache();
    }

    /** Все свойства ИБ.
     *
     * @param int $iblockId
     *
     * @return array
     */
    public function getPropertiesIblockID(int $iblockId)
    {
        $arResult = [];

        $properties = CIBlockProperty::GetList(
            ['sort' => 'asc'],
            ['IBLOCK_ID' =>$iblockId]
        );

        while ($prop_fields = $properties->GetNext()) {
            $arResult[$prop_fields['CODE']] = $prop_fields;
        }

        return $arResult;
    }

    /**
     * Все свойства ИБ из кэша.
     *
     * @param string $iblockID - ID инфоблока
     *
     * @return mixed
     */
    public function getPropertiesIblockIDCached($iblockID)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return CacherFacade::setCacheId($iblockID . '_getPropertiesIblockID')
            ->setCallback([$this, 'getPropertiesIblockID'])
            ->setCallbackParams($iblockID)
            ->setTtl(Constants::SECONDS_IN_WEEK)
            ->returnResultCache();
    }
}
