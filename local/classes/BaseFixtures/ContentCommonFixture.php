<?php

namespace Local\BaseFixtures;

use Prokl\BitrixFixtureGeneratorBundle\Services\Annotations\FieldParams;
use Prokl\BitrixFixtureGeneratorBundle\Services\Contracts\FixtureInterface;
use Prokl\BitrixFixtureGeneratorBundle\Services\Generators\EnumGenerator;
use Prokl\BitrixFixtureGeneratorBundle\Services\Generators\ImageGenerator;
use Prokl\BitrixFixtureGeneratorBundle\Services\Generators\LinkElementGenerator;
use Prokl\BitrixFixtureGeneratorBundle\Services\Generators\SentenceGenerator;

/**
 * Class ContentCommonFixture
 * @package Local\BaseFixtures
 *
 * @since 11.04.2021
 */
class ContentCommonFixture implements FixtureInterface
{
    /**
     * @inheritDoc
     */
    public function id() : string
    {
        return 'content.common';
    }

    /**
     * @inheritDoc
     * @FieldParams(
     *    params={
     *     "PREVIEW_PICTURE"= { "width"=400, "height"=400 },
     *     "PROPERTY_VALUES" = {
     *          "STRING"= { "length"=22 }
     *      }
     *    }
     * )
     */
    public function fixture() : array
    {
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
