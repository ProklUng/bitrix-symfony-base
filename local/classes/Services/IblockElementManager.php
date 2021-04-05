<?php

namespace Local\Services;

use CIBlock;
use CIBlockElement;
use Local\Constants;
use Local\Facades\CacherFacade;

/**
 * Class IblockElementManager
 * @package Local\Services
 */
class IblockElementManager
{
    /** Результат выборки из ИБ.
     *
     * @param array $arParams Параметры с типом и кодом инфоблока.
     * @param boolean $bDisableProperties Не добавлять в массив свойства элемента.
     * @return mixed
     */
    public function getIBlockElements(
        array $arParams = [
            'AR_ORDER',
            'AR_FILTER',
            'NAV_PARAMS',
            'AR_GROUP',
            'AR_SELECT',
        ],
        bool $bDisableProperties = false
    ): array {
        /** @var $arDefValues $arDefValues  Значения по-умолчанию. */
        $arDefValues = [
            'AR_ORDER' => ['SORT' => 'ASC'],
            'AR_FILTER' => [],
            'AR_GROUP' => false,
            'NAV_PARAMS' => false,
            'AR_SELECT' => ['*'],
        ];

        $arParams = array_merge($arDefValues, $arParams);

        $res = CIBlockElement::GetList(
            $arParams['AR_ORDER'],
            $arParams['AR_FILTER'],
            $arParams['AR_GROUP'],
            $arParams['NAV_PARAMS'],
            $arParams['AR_SELECT']
        );

        $i = 0;
        $arResult = [];

        while ($ob = $res->GetNextElement()) {
            $id = $ob->fields['ID'] ?? $i++;
            $arResult[$id] = $ob->fields;
            $arResult[$id]['PROPERTIES'] = $bDisableProperties ?: $ob->GetProperties();
        }

        return $arResult;
    }

    /**
     * Кэшированный результат выборки.
     *
     * @param array   $arParams           Параметры с типом и кодом инфоблока.
     * @param boolean $bDisableProperties Не добавлять в массив свойства элемента.
     *
     * @return mixed
     */
    public function getIBlockElementsCached(
        array $arParams = ['AR_ORDER', 'AR_FILTER', 'NAV_PARAMS', 'AR_GROUP', 'AR_SELECT'],
        bool $bDisableProperties = false
    ) : array {
        /** @noinspection PhpUndefinedMethodInspection */
        return CacherFacade::setCacheId(md5(serialize(array_values($arParams))))
            ->setCallback([$this, 'getIBlockElements'])
            ->setCallbackParams($arParams, $bDisableProperties)
            ->setTtl(Constants::SECONDS_IN_WEEK)
            ->returnResultCache();
    }

    /**
     * Первый элемент инфоблока.
     *
     * @param integer $iblockID ID инфоблока.
     *
     * @return integer
     */
    public function getFirstElementOfIblock(int $iblockID) : int
    {
        $link = CIBlockElement::GetList(
            [],
            ['ACTIVE' => 'Y', 'IBLOCK_ID' => $iblockID],
            false,
            false,
            ['ID']
        );
        if ($arElement = $link->Fetch()) {
            return $arElement['ID'];
        }

        return 0;
    }

    /**
     * Узнать версмю инфоблока.
     *
     * @param integer $iblockID Ид инфоблока.
     *
     * @return integer|null
     */
    public static function getVersionInfoblock(int $iblockID)
    {
        $res = CIBlock::GetList(
            [],
            [
                'TYPE' => 'content',
                'SITE_ID' => SITE_ID,
                'ACTIVE' => 'Y',
                'CNT_ACTIVE' => 'Y',
                'ID' => $iblockID,
            ],
            true
        );

        if ($arRes = $res->Fetch()) {
            return (int)$arRes['VERSION'];
        }

        return null;
    }
}
