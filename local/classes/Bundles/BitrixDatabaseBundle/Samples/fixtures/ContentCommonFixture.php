<?php

namespace Local\Bundles\BitrixDatabaseBundle\Samples\Fixtures;

use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureInterface;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\EnumGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\ImageGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\LinkElementGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\SentenceGenerator;

/**
 * Class ContentCommonFixture
 * @package Local\Bundles\BitrixDatabaseBundle\Samples\Fixtures
 *
 * @since 11.04.2021
 */
class ContentCommonFixture implements FixtureInterface
{
    /**
     * @inheritDoc
     */
    public function id() : string {
        return 'content.common';
    }

    /**
     * @inheritDoc
     */
    public function fixture() : array {
        return [
            'PREVIEW_PICTURE' => ImageGenerator::class,
            'PROPERTY_VALUES' => [
                'STRING' => SentenceGenerator::class,
                'FILE' => ImageGenerator::class,
                'MULTIPLE_STRING' => 'bitrix_database_bundle.multiple_string_generator',
                'MULTIPLE_FILE' => 'bitrix_database_bundle.multiple_image_generator',
                'ENUM' => EnumGenerator::class,
                'MULTIPLE_ENUM' => 'bitrix_database_bundle.multiple_enum_generator',
                'LINK' => LinkElementGenerator::class,
                'MULTIPLE_LINK' => 'bitrix_database_bundle.multiple_link_generator',
                // 'YES' => 1,
            ]
        ];
    }
}