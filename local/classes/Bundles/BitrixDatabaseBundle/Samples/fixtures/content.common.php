<?php
/**
 * Образец фикстуры. Указываются только поля, которые обрабатываются особым образом
 */

use Local\Bundles\BitrixDatabaseBundle\Services\Generators\EnumGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\HtmlGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\ImageGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\ImageIdGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\LinkElementGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\SentenceGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\UserIdGenerator;

return [
    //'PREVIEW_PICTURE' => ImageGenerator::class, // Сервис, помеченный тэгом fixture_generator.item.
    //'DETAIL_PICTURE' => ImageGenerator::class,
//    'CREATED_BY' => UserIdGenerator::class,
//    'MODIFIED_BY' => UserIdGenerator::class,
//    'PREVIEW_TEXT' => 'bitrix_database_bundle.preview_text_generator',
//    'DETAIL_TEXT' => 'bitrix_database_bundle.detail_text_generator',
//    'NAME' => 'bitrix_database_bundle.name_generator', // Alias сервиса
    'PROPERTY_VALUES' => [
        'STRING' => SentenceGenerator::class,
        'FILE' => ImageGenerator::class,
        'MULTIPLE_STRING' => 'bitrix_database_bundle.multiple_string_generator',
        'MULTIPLE_FILE' => 'bitrix_database_bundle.multiple_image_generator',
        'ENUM' => EnumGenerator::class,
        'MULTIPLE_ENUM' => 'bitrix_database_bundle.multiple_enum_generator',
        'LINK' => LinkElementGenerator::class,
        'MULTIPLE_LINK' => 'bitrix_database_bundle.multiple_link_generator',
    ]
];
