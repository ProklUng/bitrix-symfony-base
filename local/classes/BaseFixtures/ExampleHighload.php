<?php
/**
 * Образец фикстуры. Указываются только поля, которые обрабатываются особым образом
 */

use Prokl\BitrixFixtureGeneratorBundle\Services\Generators\RandomLinkElementGenerator;
use Prokl\BitrixFixtureGeneratorBundle\Services\Generators\RandomLinkSectionGenerator;
use Prokl\BitrixFixtureGeneratorBundle\Services\Generators\SentenceGenerator;

return [
    'UF_STRING' => 'bitrix_database_bundle.short_string_generator',
    'UF_LINK_SECTION' => RandomLinkSectionGenerator::class,
    'UF_LINK_ELEMENT' => RandomLinkElementGenerator::class,
];
