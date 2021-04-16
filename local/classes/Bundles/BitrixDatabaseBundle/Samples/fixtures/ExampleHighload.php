<?php
/**
 * Образец фикстуры. Указываются только поля, которые обрабатываются особым образом
 */

use Local\Bundles\BitrixDatabaseBundle\Services\Generators\RandomLinkElementGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\RandomLinkSectionGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\SentenceGenerator;

return [
    'UF_STRING' => 'bitrix_database_bundle.short_string_generator',
    'UF_LINK_SECTION' => RandomLinkSectionGenerator::class,
    'UF_LINK_ELEMENT' => RandomLinkElementGenerator::class,
];