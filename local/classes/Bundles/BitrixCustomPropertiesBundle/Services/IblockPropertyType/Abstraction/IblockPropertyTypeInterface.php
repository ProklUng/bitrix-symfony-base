<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction;

/**
 * Interface IblockPropertyTypeInterface
 *
 * Интерфейс типа свойства, который помогает объявить требуемые методы, а благодаря продуманным php-doc блокам не
 * лазить по документации Битрикса и даже быть в курсе недокументированных особенностей последнего.
 *
 * Также интерфейс помогает понять и следовать подходу с отказом от статических методов в пользу классического ООП и
 * полиморфизма.
 *
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction
 */
interface IblockPropertyTypeInterface
{
    /**
     * Инициализирует тип свойства, добавляя вызов getUserTypeDescription() при событии
     * iblock::OnIBlockPropertyBuildList
     *
     * @return void
     */
    public function init();

    /**
     * Возвращает массив с описанием типа свойства и связки с реализацией своеобразного "интерфейса" из абстрактных
     * операций, которые использует Битрикс.
     *
     * Например,
     *
     * [
     *    'PROPERTY_TYPE'        => 'N',
     *    'USER_TYPE'            => 'YesNoPropertyType',
     *    'DESCRIPTION'          => 'Признак "Да/Нет"',
     *    'GetAdminListViewHTML' => [$this, 'getAdminListViewHTML'],
     *    'GetPropertyFieldHtml' => [$this, 'getPropertyFieldHtml'],
     *    'ConvertToDB'          => [$this, 'convertToDB'],
     *    'ConvertFromDB'        => [$this, 'convertFromDB'],
     *    'GetAdminFilterHTML'   => [$this, 'getAdminFilterHTML'],
     * ]
     *
     *
     *
     * @return array
     */
    public function getUserTypeDescription();

    /**
     * Метод должен проверить корректность значения свойства и вернуть массив. Пустой, если ошибок нет, и с сообщениями
     * об ошибках, если есть.
     *
     * @param array $property
     * @param array $value
     *
     * @return array
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/CheckFields.php
     */
    public function checkFields(array $property, array $value);

    /**
     * Метод должен вернуть фактическую длину значения свойства. Этот метод нужен только для свойств значения которых
     * представляют собой сложные структуры (например массив).
     *
     * @param array $property
     * @param array $value
     *
     * @return int
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/GetLength.php
     */
    public function getLength(array $property, array $value);

    /**
     * Метод должен преобразовать значение свойства в формат пригодный для сохранения в базе данных. И вернуть массив
     * вида array("VALUE" => "...", "DESCRIPTION" => "..."). Если значение свойства это массив, то разумным будет
     * использование функции serialize. А вот Дата/время преобразуется в ODBC формат "YYYY-MM-DD HH:MI:SS". Это
     * определит возможности сортировки и фильтрации по значениям данного свойства.
     *
     * @param array $property
     * @param array $value ['VALUE' => 'mixed', 'DESCRIPTION' => 'string']
     *
     * @return array
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/ConvertToDB.php
     */
    public function convertToDB(array $property, array $value);

    /**
     * Метод должен преобразовать значение свойства из формата пригодного для сохранения в базе данных в формат
     * обработки. И вернуть массив вида array("VALUE" => "...", "DESCRIPTION" => "...").
     *
     * @param array $property
     * @param array $value ['VALUE' => 'mixed', 'DESCRIPTION' => 'string']
     *
     * @return array
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/ConvertFromDB.php
     */
    public function convertFromDB(array $property, array $value);

    /**
     * Метод должен вернуть HTML отображения элемента управления для редактирования значений свойства в
     * административной части.
     *
     * @param array $property
     * @param array $value ['VALUE' => 'mixed', 'DESCRIPTION' => 'string']
     * @param array $control
     *
     * @return string
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/GetPropertyFieldHtml.php
     */
    public function getPropertyFieldHtml(array $property, array $value, array $control);

    /**
     * Метод должен вернуть безопасный HTML отображения значения свойства в списке элементов административной части.
     *
     * @param array $property
     * @param array $value ['VALUE' => 'mixed', 'DESCRIPTION' => 'string']
     * @param array $control
     *
     * @return mixed
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/GetAdminListViewHTML.php
     */
    public function getAdminListViewHTML(array $property, array $value, array $control);

    /**
     * Метод должна вернуть безопасный HTML отображения значения свойства в публичной части сайта. Если она вернет
     * пустое значение, то значение отображаться не будет.
     *
     * @param array $property
     * @param array $value
     * @param array $control
     *
     * @return string
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/GetPublicViewHTML.php
     */
    public function getPublicViewHTML(array $property, array $value, array $control);

    /**
     * Метод должен вернуть HTML отображения элемента управления для редактирования значений свойства в публичной части
     * сайта.
     *
     * @param array $property
     * @param array $value
     * @param array $control
     *
     * @return string
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/GetPublicEditHTML.php
     */
    public function getPublicEditHTML(array $property, array $value, array $control);

    /**
     * Метод должен вернуть представление значения свойства для модуля поиска.
     *
     * @param array $property
     * @param array $value
     * @param array $control
     *
     * @return string
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/GetSearchContent.php
     */
    public function getSearchContent(array $property, array $value, array $control);

    /**
     * Метод возвращает либо массив с дополнительными настройками свойства, либо весь набор настроек, включая
     * стандартные.
     *
     * @param array $property
     *
     * @return array|string
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/PrepareSettings.php
     */
    public function prepareSettings(array $property);

    /**
     * Метод должен вернуть безопасный HTML отображения настроек свойства для формы редактирования инфоблока.
     *
     * @param array $property
     * @param array $value
     * @param array $control
     *
     * @return string
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/GetSettingsHTML.php
     */
    public function getSettingsHTML(array $property, array $value, array $control);

    /**
     * Вывод формы редактирования множественного свойства. Если отсутствует, то используется GetPropertyFieldHtml для
     * каждого значения отдельно (у множественных свойств).
     *
     * @param array $property
     * @param array $value
     * @param array $control
     *
     * @return string
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/getpropertyfieldhtmlmulty.php
     */
    public function getPropertyFieldHtmlMulty(array $property, array $value, array $control);

    /**
     * Выводит html для фильтра по свойству на административной странице списка элементов инфоблока.
     *
     * @param array $property
     * @param array $control
     *
     * @return string
     *
     * @internal Если фильтр выбран, то получить доступ к его значению можно только через $GLOBALS[$control['VALUE']]
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/getadminfilterhtml.php
     */
    public function getAdminFilterHTML(array $property, array $control);

    /**
     * Выводит html для фильтра по свойству на публичной странице списка элементов инфоблока.
     *
     * @param array $property
     * @param array $control
     *
     * @return string
     *
     * @link https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/getpublicfilterhtml.php
     */
    public function getPublicFilterHTML(array $property, array $control);

    /**
     * Управляет отображением фильтра по свойству в списке элементов.
     *
     * @param array $property
     * @param string $controlName Название инпута.
     * @param array $filter Ссылка на массив параметров внешнего вида фильтра по свойству.
     *  Возможные значения 'type':
     *  <ul>
     *      <li>string</li>
     *      <li>number</li>
     *      <li>list</li>
     *      <li>custom_entity</li>
     *      <li>date</li>
     *  </ul>
     *  Если type=list, то в items массив ['значение' => 'заголовок'].
     *  Если type=date, то time=true|false|null и filterable=''
     *  Если type=custom_entity, то property=$property, customRender=string callable , customFilter = void callable.
     *     (см. \CIBlockPropertyElementAutoComplete::GetUIFilterProperty )
     *
     *  'filterable' обычно пустая строка или '?', когда type=string.
     *
     * @return void
     *
     * @see \CIBlockPropertyElementAutoComplete::GetUIFilterProperty
     * @see \CIBlockPropertyDateTime::GetUIFilterProperty
     * @see \CIBlockPropertyDate::GetUIFilterProperty
     * @see \CIBlockPropertySequence::GetUIFilterProperty
     * @see \CIBlockPropertyHTML::GetUIFilterProperty
     * @see \CIBlockPropertyElementList::GetUIFilterProperty
     *
     * @internal Официальной документации по методу не существует.
     */
    public function getUIFilterProperty(array $property, $controlName, array &$filter);
}