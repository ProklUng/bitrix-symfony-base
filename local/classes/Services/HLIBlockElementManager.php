<?php

namespace Local\Services;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Local\Constants;
use Local\Facades\CacherFacade;

/**
 * Class HLIBlockElementManager
 * @package Local\Services
 */
class HLIBlockElementManager
{
    /** Результат выборки из HL ИБ.
     *
     * @param string $sHLIblockCode Код HL блока.
     * @param array $arParams Параметры выборки.
     *
     * @return mixed
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getIBlockElements(
        string $sHLIblockCode,
        array $arParams = [
            'AR_ORDER',
            'AR_FILTER',
            'AR_GROUP',
            'AR_SELECT',
        ]
    ): array {

        // Если не передали код HL блока, то прекращаем работу.
        if (!$sHLIblockCode) {
            return [];
        }

        /** @var $arDefValues $arDefValues  Значения по-умолчанию. */
        $arDefValues = [
            'AR_ORDER' => ['ID' => 'ASC'],
            'AR_FILTER' => [],
            'AR_SELECT' => ['*'],
        ];

        $arParams = array_merge($arDefValues, $arParams);

        $sHlClassName = $this->getHLBlockClassByCode($sHLIblockCode);

        $obData = $sHlClassName::getList(
            [
                'select' => $arParams['AR_SELECT'],
                'order' => $arParams['AR_ORDER'],
                'filter' => $arParams['AR_FILTER'],
            ]
        );

        $arResult = [];

        while ($arData = $obData->fetch()) {
            $idElement = $arData['ID'];
            $arResult[$idElement] = $arData;
        }

        return $arResult;
    }


    /**
     *  Кэшированный результат выборки из HL блока.
     *
     * @param string $sCodeHLblock Код HL блока.
     * @param array  $arParams     Параметры с типом и кодом инфоблока.
     *
     * @return mixed
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getIBlockElementsCached(
        string $sCodeHLblock,
        array $arParams = ['AR_ORDER', 'AR_FILTER', 'AR_SELECT']
    ) : array {
        return CacherFacade::setCacheId($sCodeHLblock.serialize(array_values($arParams)))
            ->setCallback([$this, 'getIBlockElements'])
            ->setCallbackParams($sCodeHLblock, $arParams)
            ->setTtl(Constants::SECONDS_IN_WEEK)
            ->returnResultCache();
    }

    /**
     * Получить название класса HL блока по его коду
     *
     * @param string $sCodeHLblock Код HL блока.
     *
     * @return string|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getHLBlockClassByCode(
        string $sCodeHLblock
    ): ?string {
        $obHlblock = HighloadBlockTable::getList(
            [
                'filter' => ['=NAME' => $sCodeHLblock],
            ]
        )->fetch();

        if (!$obHlblock) {
            $arResult['TERMS_DESCRIPTION'] = [];
        }

        try {
            $sHlClassName = (HighloadBlockTable::compileEntity($obHlblock))->getDataClass();
        } catch (SystemException $e) {
            return false;
        }

        return $sHlClassName;
    }
}
