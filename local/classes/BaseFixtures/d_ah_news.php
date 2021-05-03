<?php
/**
 * Образец фикстуры. Указываются только поля, которые обрабатываются особым образом
 */

use Prokl\BitrixFixtureGeneratorBundle\Services\Generators\HtmlGenerator;
use Prokl\BitrixFixtureGeneratorBundle\Services\Generators\ImageIdGenerator;
use Prokl\BitrixFixtureGeneratorBundle\Services\Generators\UserIdGenerator;

return [
    'IMAGE' => ImageIdGenerator::class, // Сервис, помеченный тэгом fixture_generator.item.
    'CREATED_BY' => UserIdGenerator::class,
    'MODIFIED_BY' => UserIdGenerator::class,
    'TEXT' => HtmlGenerator::class,
    'TEXT_TEXT_TYPE' => 'html',
    'TITLE' => 'bitrix_database_bundle.title_generator', // Alias сервиса
];
