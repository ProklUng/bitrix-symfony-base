<?php

namespace Local\Bundles\BitrixDatabaseBundle\Samples\Fixtures;

use Local\Bundles\BitrixDatabaseBundle\Services\Annotations\FieldParams;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureInterface;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\HtmlGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\ImageIdGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\UserIdGenerator;

/**
 * Class TableDaNewsFixture
 * @package Local\Bundles\BitrixDatabaseBundle\Samples\Fixtures
 *
 * @since 11.04.2021
 */
class TableDaNewsFixture implements FixtureInterface
{
    /**
     * @inheritDoc
     */
    public function id() : string {
        return 'd_ah_news';
    }

    /**
     * @inheritDoc
     *
     * @FieldParams(
     *    params={
     *     "TITLE"= { "length"=20 },
     *    }
     * )
     */
    public function fixture() : array {
        return [
            'IMAGE' => ImageIdGenerator::class, // Сервис, помеченный тэгом fixture_generator.item.
            'CREATED_BY' => UserIdGenerator::class,
            'MODIFIED_BY' => UserIdGenerator::class,
            'TEXT' => HtmlGenerator::class,
            'TEXT_TEXT_TYPE' => 'html',
            'TITLE' => 'bitrix_database_bundle.title_generator', // Alias сервиса
        ];
    }
}