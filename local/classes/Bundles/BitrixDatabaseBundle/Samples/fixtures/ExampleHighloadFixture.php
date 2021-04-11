<?php

namespace Local\Bundles\BitrixDatabaseBundle\Samples\Fixtures;

use Local\Bundles\BitrixDatabaseBundle\Services\Annotations\FieldParams;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureInterface;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\RandomLinkElementGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\RandomLinkSectionGenerator;

/**
 * Class ExampleHighloadFixture
 * @package Local\Bundles\BitrixDatabaseBundle\Samples\Fixtures
 *
 * @since 11.04.2021
 */
class ExampleHighloadFixture implements FixtureInterface
{
    /**
     * @inheritDoc
     */
    public function id() : string {
        return 'ExampleHighload';
    }

    /**
     * @inheritDoc
     * @FieldParams(
     *    params={
     *     "UF_STRING"= { "length"=10 },
     *    }
     * )
     */
    public function fixture() : array {
        return [
            'UF_STRING' => 'bitrix_database_bundle.short_string_generator',
            'UF_LINK_SECTION' => RandomLinkSectionGenerator::class,
            'UF_LINK_ELEMENT' => RandomLinkElementGenerator::class,
        ];
    }
}
