<?php

namespace Local\Services\Utils\Migrations;

use CIBlock;
use CIBlockProperty;
use Exception;
use Bitrix\Main\SiteTable;

/**
 * Trait IblockPropertyTrait
 * @package Local\Services\Utils\Migrations
 *
 * @since 11.04.2021
 */
trait IblockPropertyTrait
{
    /**
     * Создает новое пользовательское свойство инфоблока.
     *
     * @param string $iblock
     * @param array  $data
     *
     * @return array
     *
     * @throws Exception
     */
    protected function iblockPropertyCreate($iblock, array $data)
    {
        $return = [];

        $iblock = $this->iblockLocate($iblock);

        if (empty($data['CODE'])) {
            throw new Exception('You must set property CODE');
        }
        $res = CIBlockProperty::getList([], [
            'CODE' => $data['CODE'],
            'IBLOCK_ID' => $iblock['ID'],
            'CHECK_PERMISSIONS' => 'N',
        ]);
        if ($ob = $res->fetch()) {
            throw new Exception(
                "Property {$data['CODE']}({$ob['ID']}) for iblock {$iblock['CODE']}({$iblock['ID']}) already exists"
            );
        }

        $ib = new CIBlockProperty();
        $id = $ib->add(array_merge([
            'IBLOCK_ID' => $iblock['ID'],
            'CODE' => $data['CODE'],
            'XML_ID' => $data['CODE'],
            'ACTIVE' => 'Y',
        ], $data));

        if ($id) {
            $return[] = "Property {$data['CODE']}($id) for iblock {$iblock['CODE']}({$iblock['ID']}) added";
        } else {
            throw new Exception(
                "Can't create property {$data['CODE']}. Error: {$ib->LAST_ERROR}"
            );
        }

        return $return;
    }

    /**
     * Обновляет указанное пользовательское свойство инфоблока.
     *
     * @param string $iblock
     * @param array  $data
     *
     * @return array
     *
     * @throws Exception
     */
    protected function iblockPropertyUpdate($iblock, array $data)
    {
        $return = [];

        $iblock = $this->iblockLocate($iblock);

        if (empty($data['CODE'])) {
            throw new Exception('You must set property CODE');
        }

        $res = CIBlockProperty::getList([], [
            'CODE' => $data['CODE'],
            'IBLOCK_ID' => $iblock['ID'],
            'CHECK_PERMISSIONS' => 'N',
        ]);
        if ($ob = $res->fetch()) {
            if (!empty($ob['USER_TYPE']) && empty($data['USER_TYPE'])) {
                $data['USER_TYPE'] = $ob['USER_TYPE'];
            }
            $ib = new CIBlockProperty();
            $id = $ib->update($ob['ID'], $data);
            if ($id) {
                $return[] = "Property {$data['CODE']}({$ob['ID']}) for iblock {$iblock['CODE']}({$iblock['ID']}) updated";
            } else {
                throw new Exception(
                    "Can't update {$data['CODE']} property. Error: {$ib->LAST_ERROR}"
                );
            }
        } else {
            throw new Exception(
                "Can't find {$data['CODE']} property for iblock {$iblock['CODE']}({$iblock['ID']})"
            );
        }

        return $return;
    }

    /**
     * Удаляет указанное пользовательское свойство инфоблока.
     *
     * @param string $iblock
     * @param string $code
     *
     * @return array
     *
     * @throws Exception
     */
    protected function iblockPropertyDelete($iblock, $code)
    {
        $return = [];

        $iblock = $this->iblockLocate($iblock);

        $res = CIBlockProperty::getList([], [
            'CODE' => $code,
            'IBLOCK_ID' => $iblock['ID'],
            'CHECK_PERMISSIONS' => 'N',
        ]);
        if ($ob = $res->fetch()) {
            if (CIBlockProperty::delete($ob['ID'])) {
                $return[] = "Property {$code} for iblock {$iblock['CODE']}({$iblock['ID']}) deleted";
            } else {
                throw new Exception(
                    "Can't delete iblock property {$code} for iblock {$iblock['CODE']}({$iblock['ID']})"
                );
            }
        } else {
            throw new Exception(
                "Can't find property {$code} for iblock {$iblock['CODE']}({$iblock['ID']})"
            );
        }

        return $return;
    }

    /**
     * Умный поиск инфоблока. Если в параметр переданы цифры, то ищет по идентификатору,
     * если цифры и буквы, то по коду. Возвращает массив полей инфоблока.
     *
     * @param string $id
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function iblockLocate($id)
    {
        if (empty($id)) {
            throw new Exception('Id parameter must not be empty');
        } elseif (!is_numeric($id)) {
            $findByCode = $this->iblockGetIdByCode($id);
            if ($findByCode === null) {
                throw new Exception("Can't find iblock by code: {$id}");
            }
            $id = $findByCode;
        }

        $iblock = $this->iblockGetById($id);
        if ($iblock === null) {
            throw new Exception("Can't find iblock by id: {$id}");
        }

        return $iblock;
    }

    /**
     * Возвращает информацию об инфоблоке по его идентификатору.
     *
     * @param integer $id
     *
     * @return array|null
     */
    protected function iblockGetById($id)
    {
        $res = CIBlock::getList([], [
            'ID' => (int) $id,
            'CHECK_PERMISSIONS' => 'N',
        ]);

        return $res->fetch() ?: null;
    }

    /**
     * Возвращает идентификатор инфоблока по его коду.
     *
     * @param string $code
     * @param string $siteId
     *
     * @return string|null
     */
    protected function iblockGetIdByCode(string $code, ?string $siteId = null)
    {
        $siteId = $siteId ?: $this->iblockGetDefaultSiteId();
        $res = CIBlock::getList([], [
            'CODE' => $code,
            'CHECK_PERMISSIONS' => 'N',
            'SITE_ID' => $siteId,
        ]);
        $iblock = $res->fetch();

        return !empty($iblock['ID']) ? $iblock['ID'] : null;
    }

    /**
     * Возвращает идентификатор для сайта по умолчанию.
     *
     * @return string
     *
     * @throws Exception
     */
    protected function iblockGetDefaultSiteId(): string
    {
        $return = null;
        $res = SiteTable::getRow(['filter' => ['DEF' => 'Y']]);
        if (!$res) {
            $res = SiteTable::getRow(['order' => ['SORT' => 'asc']]);
            if (!$res) {
                throw new Exception('Can not find default site for iblock');
            }
        }

        return $res['LID'];
    }
}
