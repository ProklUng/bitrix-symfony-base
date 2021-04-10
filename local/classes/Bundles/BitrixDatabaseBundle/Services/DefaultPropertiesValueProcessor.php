<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services;

use Local\Bundles\BitrixDatabaseBundle\Services\Generators\EnumGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\ImageGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\IntGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\LinkElementGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\LinkSectionsGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\RandomLinkElementGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\RandomLinkSectionGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\StringGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\YesNoGenerator;
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
     * @var string[] $map Базовые свойства.
     */
    private $map = [
        'S' => [
            // MULTIPLE => Y | N.
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
     * @var array[] $customPropertyMap Кастомные свойства.
     */
    private $customPropertyMap = [
        // Признак Да-нет.
        'Local\\Bundles\\BitrixCustomPropertiesBundle\\Services\\CustomProperties\\YesNoType' => [
            'N' => YesNoGenerator::class,
            'Y' => null,
        ]
    ];

    /**
     * @var \string[][] $hlFields Поля HL блоков.
     */
    private $hlFields = [
        'iblock_section' => [
            'N' => RandomLinkSectionGenerator::class,
            'Y' => 'bitrix_database_bundle.multiple_link_section_generator',
        ],
        'iblock_element' => [
            'N' => RandomLinkElementGenerator::class,
            'Y' => 'bitrix_database_bundle.multiple_link_element_generator',
        ],
        'string' => [
            'N' => StringGenerator::class,
            'Y' => 'bitrix_database_bundle.multiple_string_generator',
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
            $propType = $propertyData['PROPERTY_TYPE'];
            $multiple = $propertyData['MULTIPLE'];

            if ($propertyData['USER_TYPE'] === null) {
                $result[$propertyData['CODE']] = $this->map[$propType][$multiple];
                continue;
            }

            // Кастомные свойства.
            $propType = $propertyData['USER_TYPE'];
            $result[$propertyData['CODE']] = $this->customPropertyMap[$propType][$multiple];
        }

        return $result;
    }

    /**
     * Получить карту генераторов свойств HL блоков.
     *
     * @param array $hlBlockProps ID инфоблока.
     *
     * @return array
     */
    public function getMapHl(array $hlBlockProps) : array
    {
        $result = [];

        foreach ($hlBlockProps as $propName => $propertyData) {
            $propType = $propertyData['USER_TYPE_ID'];
            $multiple = $propertyData['MULTIPLE'];

            $result[$propName] = $this->hlFields[$propType][$multiple];
        }

        return $result;
    }
}