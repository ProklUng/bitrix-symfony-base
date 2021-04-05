<?php

namespace Local\Bitrix\Orm;

use Bitrix\Main\FileTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Ps\D7\Fields\FileField;

/**
 * Class ExampleModelOrmTable
 * @package Local\Bitrix\Orm
 *
 * @since 11.02.2021
 *
 * @internal
 * Если таблица с отношениями, то нужно отнаследоваться от getMap
 * Вызвать родительский метод и подмешать нужное в результат.
 * $arRelations = [
 * 'CREATED'=>new Reference(
 * 'CREATED',
 * \Bitrix\Main\UserTable::class,
 * array('=this.CREATED_BY' => 'ref.ID')
 * ),
 * 'CATALOG_GROUP'=>new Reference(
 * 'CATALOG_GROUP',
 * \Bitrix\Catalog\GroupTable::class,
 * array('=this.CATALOG_GROUP_ID' => 'ref.ID'))
 * ];
 * return array_merge($arMap, $arRelations);
 *
 */
class ExampleModelOrmTable extends AbstractBaseModel
{
    /**
     * @var string $table Таблица.
     */
    private static $table = 'd_ah_news';

    /**
     * ExampleModelOrmTable constructor.
     *
     * @param string $table Название таблицы.
     */
    public function __construct(string $table = '')
    {
        if ($table !== '') {
            static::$table = $table;
        }
    }

    /**
     * @inheritDoc
     */
    public static function getTableName(): string
    {
        return static::$table;
    }

    /**
     * @inheritDoc
     */
    public static function getMap() : array
    {
        $fields = parent::getMap();

        $fields['IMAGE'] = new FileField('IMAGE', ['column_name' => 'IMAGE']);
        $arRelations = [
            'CREATED'=>new Reference(
                'CREATED',
                FileTable::class,
                ['=this.IMAGE' => 'ref.ID']
            ),
        ];
        return array_merge($fields, $arRelations);
    }
}
