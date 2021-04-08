<?php

namespace Local\Bitrix\Orm;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\SystemException;

/**
 * Class AbstractBaseModel
 * @package Local\Bitrix\Orm
 *
 * @since 11.02.2021
 * @since 14.02.2021 Глобальная переработка.
 * @since 17.03.2021 Косметический рефакторинг.
 */
abstract class AbstractBaseModel extends DataManager
{
    /**
     * @var boolean $caching Кэшировать запрос.
     */
    protected static $caching = true;

    /**
     * @const int CACHE_TTL Время жизни кэша.
     */
    protected const CACHE_TTL = 86400;

    /**
     * @return array
     * @throws SystemException | SqlQueryException | LoaderException
     */
    public static function getTableDescription(): array
    {
        $arResult = [];
        if (Loader::includeModule('main')) {
            $connection = Application::getInstance()->getConnection();
            $sql = 'SELECT * ';
            $sql .= 'FROM INFORMATION_SCHEMA.COLUMNS ';
            $sql .= 'WHERE TABLE_SCHEMA = "'.$connection->getDatabase().'" ';
            $sql .= 'AND TABLE_NAME = "'.static::getTableName().'";';
            $db = $connection->query($sql);
            while ($result = $db->fetch()) {
                $arResult[] = $result;
            }
        }

        return $arResult;
    }

    /**
     * @return array
     * @throws LoaderException | SystemException
     */
    public static function getMap(): array
    {
        $arResult = [];
        $arFieldsDescription = static::getTableDescription();

        if (Loader::includeModule('main') && count($arFieldsDescription) > 0) {
            $cache = Cache::createInstance();
            if ($cache->initCache(
                static::CACHE_TTL,
                md5('getMysqlMap'.static::getTableName()),
                'vs/getMysqlMap/'.static::getTableName())) {
                $arResult = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                foreach ($arFieldsDescription as $arItem) {
                    $obField = null;
                    switch ($arItem['DATA_TYPE']) {
                        case 'numeric':
                        case 'int':
                            $obField = (new IntegerField($arItem['COLUMN_NAME']));
                            break;
                        case 'tinyint':
                            $obField = (new BooleanField($arItem['COLUMN_NAME']));
                            break;
                        case 'float':
                        case 'decimal':
                            $obField = (new FloatField($arItem['COLUMN_NAME']));
                            break;
                        case 'char':
                        case 'varchar':
                            $obField = (new StringField($arItem['COLUMN_NAME']));
                            break;
                        case 'text':
                        case 'longtext':
                        case 'mediumtext':
                            $obField = (new TextField($arItem['COLUMN_NAME']));
                            break;
                        case 'datetime':
                        case 'time':
                        case 'timestamp':
                            $obField = (new DatetimeField($arItem['COLUMN_NAME']));
                            break;
                        case 'date':
                        case 'year':
                            $obField = (new DateField($arItem['COLUMN_NAME']));
                            break;
                        default:
                            break;
                    }

                    if (is_object($obField)) {
                        if ($arItem['EXTRA'] === 'auto_increment') {
                            $obField->configureAutocomplete(true);
                        }
                        if ($arItem['COLUMN_KEY'] === 'PRI') {
                            $obField->configurePrimary(true);
                        }
                        if ($arItem['COLUMN_DEFAULT'] !== '' && !empty($arItem['COLUMN_DEFAULT'])) {
                            $obField->configureDefaultValue($arItem['COLUMN_DEFAULT']);
                        }
                    }

                    if (!is_null($obField)) {
                        $arResult[$arItem['COLUMN_NAME']] = $obField;
                    }
                }

                $cache->endDataCache($arResult);
            }
        }

        return $arResult;
    }
}
