<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services;

use Local\Bundles\BitrixDatabaseBundle\Services\Generators\CodeGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\DateGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\ImageGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\UserIdGenerator;

/**
 * Class StandartIblockDataMapper
 * @package Local\Bundles\BitrixDatabaseBundle\Services
 *
 * @since 08.04.2021
 */
class StandartIblockDataMapper
{
    /**
     * @var string[] $map
     */
    private $map = [
        'PREVIEW_PICTURE' => 'bitrix_database_bundle.preview_picture_generator',
        'DETAIL_PICTURE' => 'bitrix_database_bundle.detail_picture_generator', // Сервис, помеченный тэгом fixture_generator.item.
        'ACTIVE_FROM' => DateGenerator::class,
        'CREATED_BY' => UserIdGenerator::class,
        'MODIFIED_BY' => UserIdGenerator::class,
        'PREVIEW_TEXT' => 'bitrix_database_bundle.preview_text_generator',
        'PREVIEW_TEXT_TYPE' => 'html',
        'DETAIL_TEXT' => 'bitrix_database_bundle.detail_text_generator',
        'DETAIL_TEXT_TYPE' => 'html',
        'NAME' => 'bitrix_database_bundle.name_generator', // Alias сервиса
        'CODE' => CodeGenerator::class,
    ];

    /**
     * @var string[] $sectionMap
     */
    private $sectionMap = [
        'NAME' => 'bitrix_database_bundle.name_generator', // Alias сервиса
        'CODE' => CodeGenerator::class,
        'PICTURE' => 'bitrix_database_bundle.preview_picture_generator',
        'DETAIL_PICTURE' => 'bitrix_database_bundle.detail_picture_generator',
        'DESCRIPTION' => 'bitrix_database_bundle.preview_text_generator',
        'DESCRIPTION_TYPE' => 'html',
        'MODIFIED_BY' => UserIdGenerator::class,
    ];

    /**
     * @return string[]
     */
    public function getSectionMap(): array
    {
        return $this->sectionMap;
    }

    /**
     * @return string[]
     */
    public function getMap() : array
    {
        return $this->map;
    }
}
