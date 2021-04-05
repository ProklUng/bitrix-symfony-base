<?php

namespace Local\Services;

use Bitrix\Main\ArgumentException;
use CFile;
use CIBlock;
use Local\Constants;
use Local\Facades\CacherFacade;

/**
 * Class IblockManager
 * @package Local\Services
 */
class IblockManager
{
    /** ID инфоблока по коду.
     *
     * @param string $iblockType
     * @param string $iblockCode
     * @return mixed
     *
     * @throws ArgumentException
     */
    public function getIBlockIdByCode(string $iblockType, string $iblockCode)
    {
        $res = CIBlock::GetList(
            [],
            ['ACTIVE' => 'Y', 'TYPE' => $iblockType, 'CODE' => $iblockCode]
        );
        $arResult = $res->Fetch();
        if ($arResult['ID'] > 0) {
            return $arResult['ID'];
        }

        throw new ArgumentException('Инфоблок '.$iblockCode.' не найден', $iblockCode);
    }

    /**
     * ID инфоблока по его коду из кэша.
     *
     * @param string $iblockType - тип инфоблока
     * @param string $iblockCode - код инфоблока
     *
     * @return integer
     */
    public function getIBlockIdByCodeCached($iblockType, $iblockCode)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $cacher = CacherFacade::setCacheId($iblockType . $iblockCode)
                               ->setCallback([$this, 'getIBlockIdByCode'])
                               ->setCallbackParams($iblockType, $iblockCode)
                               ->setTtl(Constants::SECONDS_IN_WEEK);


        return $cacher->returnResultCache();
    }

    /**
     * Описание инфоблока.
     *
     * @param string $typeIblock
     * @param string $codeIblock
     * @return mixed
     * @throws ArgumentException
     */
    public function getIBlockDescriptionByCode(string $typeIblock, string $codeIblock)
    {
        $res = CIBlock::GetList(
            [],
            ['ACTIVE' => 'Y', 'TYPE' => $typeIblock, 'CODE' => $codeIblock]
        );

        $arResult = $res->Fetch();
        if ($arResult['ID'] > 0) {
            return $arResult['DESCRIPTION'];
        }

        throw new ArgumentException('Инфоблок '.$codeIblock.' не найден', $codeIblock);
    }

    /**
     * Описание инфоблока по его коду из кэша.
     *
     * @param string $iblockType  Тип инфоблока.
     * @param string $iblockCode  Код инфоблока.
     *
     * @return string
     */
    public function getIBlockDescriptionByCodeCached($iblockType, $iblockCode)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $cacher = CacherFacade::setCacheId('iblockDescription' . $iblockType . $iblockCode)
            ->setCallback([$this, 'getIBlockDescriptionByCode'])
            ->setCallbackParams($iblockType, $iblockCode)
            ->setTtl(Constants::SECONDS_IN_WEEK);


        return $cacher->returnResultCache();
    }

    /**
     * Описание инфоблока по ID.
     *
     * @param int $iblockId
     *
     * @return string
     */
    public function getDescriptionById(int $iblockId) : string
    {
        $description = $this->getFieldValue($iblockId, 'DESCRIPTION');

        return !empty($description) ? $description : '';
    }

    /**
     * Описание инфоблока по ID.
     *
     * @param int $iblockId
     *
     * @return string
     */
    public function getDescriptionByIdCached(int $iblockId) : string
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $cacher = CacherFacade::setCacheId('iblockDescription' . $iblockId)
            ->setCallback([$this, 'getDescriptionById'])
            ->setCallbackParams($iblockId)
            ->setTtl(Constants::SECONDS_IN_WEEK);

        return $cacher->returnResultCache();
    }

    /**
     * Название инфоблока по ID.
     *
     * @param integer $iblockId
     *
     * @return string
     */
    public function getNameById(int $iblockId) : string
    {
        $name = $this->getFieldValue($iblockId, 'NAME');

        return !empty($name) ? $name : '';
    }

    /**
     * Название инфоблока по ID из кэша.
     *
     * @param integer $iblockId
     *
     * @return string
     */
    public function getNameByIdCached(int $iblockId) : string
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $cacher = CacherFacade::setCacheId('iblockName' . $iblockId)
            ->setCallback([$this, 'getNameById'])
            ->setCallbackParams($iblockId)
            ->setTtl(Constants::SECONDS_IN_WEEK);

        return $cacher->returnResultCache();
    }

    /**
     * Название инфоблока по коду.
     *
     * @param string $iblockCode
     * @return string
     */
    public function getNameByCode(string $iblockCode) : string
    {
        $query = CIBlock::GetList(
            [],
            ['ACTIVE' => 'Y', 'CODE' => $iblockCode]
        );

        $arResult = $query->Fetch();

        return !empty($arResult['NAME']) ? $arResult['NAME'] : '';
    }

    /**
     * Получить URL инфоблока.
     *
     * @param array $arParams
     *
     * @return mixed
     * @throws ArgumentException
     */
    public function getIblockUrlByCode($arParams = ['TYPE', 'CODE'])
    {
        $res = CIBlock::GetList(
            [],
            ['ACTIVE' => 'Y', 'TYPE' => $arParams['TYPE'], 'CODE' => $arParams['CODE']]
        );
        $arResult = $res->Fetch();

        if ($arResult['ID'] > 0) {
            return str_replace('#SITE_DIR#', '', $arResult['LIST_PAGE_URL']);
        }

        throw new ArgumentException('Инфоблок '.$arParams['CODE'].' не найден', $arParams['CODE']);
    }

    /**
     * Кэшированный ответ - URL инфоблока по коду.
     *
     * @param string $sIBlockType Тип инфоблока.
     * @param string $sIBlockCode Код инфоблока.
     *
     * @return mixed
     */
    public function getIblockUrlByCodeCached($sIBlockType, $sIBlockCode)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $cacher = CacherFacade::setCacheId('iblockurl'.$sIBlockType . $sIBlockCode)
            ->setCallback([$this, 'getIblockUrlByCode'])
            ->setCallbackParams($sIBlockType, $sIBlockCode)
            ->setTtl(Constants::SECONDS_IN_WEEK);

        return $cacher->returnResultCache();
    }

    /**
     * Получить картинку инфоблока.
     *
     * @param integer $iblockId ID инфоблока.
     *
     * @return string
     */
    public function getImageIB(int $iblockId) : string
    {
        $iPictureId = $this->getImageIbId($iblockId);
        $sUrlPicture = CFile::GetPath($iPictureId);

        return $sUrlPicture ?: '';
    }

    /**
     * Получить ID картинки инфоблока.
     *
     * @param integer $iblockId ID инфоблока.
     *
     * @return integer
     */
    public function getImageIbId(int $iblockId) : int
    {
        $iPictureId = $this->getFieldValue($iblockId, 'PICTURE');

        return $iPictureId ?: 0;
    }

    /**
     * Получить ID картинки инфоблока. Кэширование.
     *
     * @param integer $iblockId ID инфоблока.
     *
     * @return mixed
     */
    public function getImageIbIdCached(int $iblockId)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $cacher = CacherFacade::setCacheId('iblockimage'.$iblockId)
            ->setCallback([$this, 'getImageIbId'])
            ->setCallbackParams($iblockId)
            ->setTtl(Constants::SECONDS_IN_WEEK);

        return $cacher->returnResultCache();
    }

    /**
     * Получить ID картинки инфоблока по его коду.
     *
     * @param string $iblockCode Код инфоблока.
     *
     * @return integer
     */
    public function getImageByCode(string $iblockCode) : int
    {
        $query = CIBlock::GetList(
            [],
            ['ACTIVE' => 'Y', 'CODE' => $iblockCode]
        );

        $arResult = $query->Fetch();

        return !empty($arResult['PICTURE']) ? $arResult['PICTURE'] : 0;
    }

    /**
     * Поле из свойств инфоблока.
     *
     * @param int $iblockId
     * @param string $field
     *
     * @return mixed
     */
    private function getFieldValue(int $iblockId, string $field)
    {
        $arData = CIBlock::GetArrayByID($iblockId);

        return $arData[$field];
    }
}
