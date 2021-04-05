<?php

use Arrilot\BitrixMigrations\BaseMigrations\BitrixMigration;
use Arrilot\BitrixMigrations\Exceptions\MigrationException;

/**
 * Класс миграции для привязки шаблона к сайту.
 * Class Query20181127190135801159
 */
class SettingsForSite20181124190135801159 extends BitrixMigration
{
    /**
     * Run the migration.
     *
     * @return mixed
     * @throws \Exception
     */
    public function up()
    {
        // Привязываем шаблон к сайту.
        $arFields = array(
            "ACTIVE" => "Y",
            "SORT" => 1,
            "DEF" => "Y",
            "NAME" => 'Базовая сборка сайта на битриксе',
            "DIR" => '/',
            "FORMAT_DATE" => "DD.MM.YYYY",
            "FORMAT_DATETIME" => "DD.MM.YYYY HH:MI:SS",
            "CHARSET" => "UTF-8",
            "SITE_NAME" => 'Базовая сборка сайта на битриксе',
            "SERVER_NAME" => "site.loc",
            "EMAIL" => "",
            "LANGUAGE_ID" => "ru",
            "DOC_ROOT" => "",
            "DOMAINS" => "site.ru
            site.loc
            site.test.ru",

            "TEMPLATE" => array(
                array(
                    "TEMPLATE" => "main",
                    "SORT" => 1,
                    "CONDITION" => ""
                )
            )
        );
        $obSite = new \CSite;
        $obSite->Update('s1', $arFields);

        if (strlen($obSite->LAST_ERROR) > 0) {
            throw new MigrationException('Ошибка при добавлении свойства инфоблока ' . $obSite->LAST_ERROR);
        }
    }

    /**
     * Reverse the migration.
     *
     * @return mixed
     * @throws \Exception
     */
    public function down()
    {
        //
    }
}
