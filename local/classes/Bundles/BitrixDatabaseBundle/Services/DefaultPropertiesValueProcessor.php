<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services;

use Local\Bundles\BitrixDatabaseBundle\Services\Generators\EnumGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\ImageGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\IntGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\LinkElementGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\LinkSectionsGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\StringGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\IblockProperties;

/**
 * Class DefaultPropertiesValueProcessor
 * @package Local\Bundles\BitrixDatabaseBundle\Services
 *
 * @since 10.04.2021
 */
class DefaultPropertiesValueProcessor
{
    /**
     * @var IblockProperties $propertiesProcessor Менеджер свойств инфоблоков.
     */
    private $propertiesProcessor;

    /**
     * @var string[] $map
     */
    private $map = [
        'S' => [
            'N' => StringGenerator::class,
            'Y' => 'bitrix_database_bundle.multiple_string_generator', // Множественное свойство
        ],
        'N' => [
            'N' => IntGenerator::class,
            'Y' => 'bitrix_database_bundle.multiple_int_generator',
        ],
        'L' => [
            'N' => EnumGenerator::class,
            'Y' => 'bitrix_database_bundle.multiple_enum_generator',
        ],
        'F' => [
            'N' => ImageGenerator::class,
            'Y' => 'bitrix_database_bundle.multiple_image_generator',
        ],
        'E' => [
            'N' => LinkElementGenerator::class,
            'Y' => 'bitrix_database_bundle.multiple_link_generator',
        ],
        'G' => [
            'N' => LinkSectionsGenerator::class,
            'Y' => 'bitrix_database_bundle.multiple_link_element_section_generator',
        ],
    ];

    /**
     * DefaultPropertiesValueProcessor constructor.
     *
     * @param IblockProperties $propertiesProcessor Менеджер свойств инфоблоков.
     */
    public function __construct(IblockProperties $propertiesProcessor)
    {
        $this->propertiesProcessor = $propertiesProcessor;
    }

    /**
     * Получить карту генераторов свойств.
     *
     * @param integer $idIblock ID инфоблока.
     *
     * @return array
     */
    public function getMap(int $idIblock) : array
    {
        $propsData = $this->propertiesProcessor->getAllProperties($idIblock);
        $result = [];

        foreach ($propsData as $propertyData) {
            if ($propertyData['USER_TYPE'] !== null) {
                continue;
            }

            $propType = $propertyData['PROPERTY_TYPE'];
            $multiple = $propertyData['MULTIPLE'];

            $result[$propertyData['CODE']] = $this->map[$propType][$multiple];
        }

        return $result;
    }
}