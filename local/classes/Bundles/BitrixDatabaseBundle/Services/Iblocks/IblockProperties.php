<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Iblocks;

use CIBlockProperty;
use CIBlockPropertyEnum;

/**
 * Class IblockProperties
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Iblocks
 *
 * @since 09.04.2021
 */
class IblockProperties
{
    /**
     * @var CIBlockPropertyEnum $cblockPropertyEnums Битриксовый CIBlockPropertyEnum.
     */
    private $cblockPropertyEnums;

    /**
     * IblockProperties constructor.
     *
     * @param CIBlockPropertyEnum $cblockPropertyEnums Битриксовый CIBlockPropertyEnum.
     */
    public function __construct(CIBlockPropertyEnum $cblockPropertyEnums)
    {
        $this->cblockPropertyEnums = $cblockPropertyEnums;
    }

    /**
     * Получает свойство инфоблока.
     *
     * @param integer       $iblockId ID инфоблока.
     * @param integer|array $code     Код или фильтр.
     *
     * @return array|boolean
     */
    public function getProperty(int $iblockId, $code)
    {
        /** @compatibility filter or code */
        $filter = is_array($code) ? $code : [
            'CODE' => $code,
        ];

        $filter['IBLOCK_ID'] = $iblockId;
        $filter['CHECK_PERMISSIONS'] = 'N';
        /* do not use =CODE in filter */
        $property = CIBlockProperty::GetList(['SORT' => 'ASC'], $filter)->Fetch();

        return $this->prepareProperty($property);
    }

    /**
     * @param integer $iblockId ID инфоблока.
     *
     * @return array
     */
    public function getAllProperties(int $iblockId) : array
    {
        $filter['IBLOCK_ID'] = $iblockId;
        $filter['CHECK_PERMISSIONS'] = 'N';

        $properties = CIBlockProperty::GetList(['SORT' => 'ASC'], $filter);
        $result = [];

        while ($prop_fields = $properties->GetNext()) {
            $result[] = $this->prepareProperty($prop_fields);
        }

        return $result;
    }

    /**
     * Получает значения списков для свойств инфоблоков.
     *
     * @param array $filter Фильтр.
     *
     * @return array
     */
    public function getPropertyEnums(array $filter = []) : array
    {
        $result = [];
        $dbres = $this->cblockPropertyEnums::GetList([
            'SORT' => 'ASC',
            'VALUE' => 'ASC',
        ], $filter);
        while ($item = $dbres->Fetch()) {
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Получает значения списков для свойства инфоблока.
     *
     * @param integer $iblockId   ID инфоблока.
     * @param integer $propertyId ID свойства.
     *
     * @return array
     */
    public function getPropertyEnumValuesById(int $iblockId, int $propertyId) : array
    {
        return $this->getPropertyEnums([
            'IBLOCK_ID' => $iblockId,
            'PROPERTY_ID' => $propertyId,
        ]);
    }

    /**
     * Получает значения списков для свойства инфоблока.
     *
     * @param integer $iblockId ID инфоблока.
     * @param string  $code     Код свойства.
     *
     * @return array
     */
    public function getPropertyEnumValuesByCode(int $iblockId, string $code) : array
    {
        return $this->getPropertyEnums([
            'IBLOCK_ID' => $iblockId,
            'CODE' => $code,
        ]);
    }

    /**
     * @param array $property Данные свойства.
     *
     * @return mixed
     */
    private function prepareProperty(array $property)
    {
        if ($property && $property['PROPERTY_TYPE'] === 'L' && $property['IBLOCK_ID'] && $property['ID']) {
            $property['VALUES'] = $this->getPropertyEnums([
                'IBLOCK_ID' => $property['IBLOCK_ID'],
                'PROPERTY_ID' => $property['ID'],
            ]);
        }
        return $property;
    }
}
