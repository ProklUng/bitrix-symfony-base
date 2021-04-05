<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\EnumProperty;

use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Result;
use CUserTypeManager;
use Exception;
use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction\IblockPropertyTypeNativeInterface;

/**
 * Class Base
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\ORM\EnumProperty
 *
 * @since 10.02.2021
 *
 * Параметры: модуль - модуль, который автоматически подключится (опционально).
 * ORM класс, из которого будут получаться данные, а так же два поля: одно будет записываться в БД,
 * а другое показываться пользователю.
 *
 * Пример ORM класса: \Bitrix\Main\GroupTable
 */
class Base implements IblockPropertyTypeNativeInterface
{
    /**
     * @const USER_TYPE_ID Тип пользовательского поля.
     */
    public const USER_TYPE_ID = 'customList';

    /**
     * @var mixed|null $property
     */
    protected $property = null;

    /**
     * Base constructor.
     *
     * @param mixed $property
     */
    public function __construct($property = null)
    {
        $this->property = $property;
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        AddEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            [__CLASS__, 'GetUserTypeDescription']
        );
    }

    /**
     * @return array
     */
    public static function GetUserTypeDescription() : array
    {
        return [
            'USER_TYPE' => static::USER_TYPE_ID,
            'USER_TYPE_ID' => static::USER_TYPE_ID,
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Кастомный список',
            'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING,
            'GetPropertyFieldHtml' => [__CLASS__, 'GetEditFormHTML'],
            'GetEditFormHTML' => [__CLASS__, 'GetEditFormHTML'],
            'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
            'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
        ];
    }

    /**
     *
     * @param array $arUserField
     *
     * @return string
     */
    public static function GetDBColumnType(array $arUserField) : string
    {
        global $DB;
        switch (strtolower($DB->type)) {
            case 'mysql':
                return 'text';
            case 'oracle':
                return 'varchar2(2000 char)';
            case 'mssql':
                return 'varchar(2000)';
        }

        return 'text';
    }

    /**
     * @param array $arUserField
     *
     * @return array
     */
    public function PrepareSettings(array $arUserField) : array
    {
        $module = trim($arUserField['USER_TYPE_SETTINGS']['MODULE']);
        $ormFieldCodeId = trim($arUserField['USER_TYPE_SETTINGS']['ORM_FIELD_CODE_ID']);
        $ormFieldCodeName = trim($arUserField['USER_TYPE_SETTINGS']['ORM_FIELD_CODE_NAME']);
        $ormClass = trim($arUserField['USER_TYPE_SETTINGS']['ORM_CLASS']);
        $height = intval($arUserField['USER_TYPE_SETTINGS']['LIST_HEIGHT']);
        $disp = $arUserField['USER_TYPE_SETTINGS']['DISPLAY'];
        $caption_no_value = trim($arUserField['USER_TYPE_SETTINGS']['CAPTION_NO_VALUE']);
        $show_no_value = $arUserField['USER_TYPE_SETTINGS']['SHOW_NO_VALUE'] === 'N' ? 'N' : 'Y';

        if ($disp !== 'CHECKBOX' && $disp !== 'LIST' && $disp !== 'UI') {
            $disp = 'LIST';
        }

        return [
            'MODULE' => $module,
            'ORM_CLASS' => $ormClass,
            'ORM_FIELD_CODE_ID' => $ormFieldCodeId,
            'ORM_FIELD_CODE_NAME' => $ormFieldCodeName,
            'DISPLAY' => $disp,
            'LIST_HEIGHT' => ($height < 1 ? 1 : $height),
            'CAPTION_NO_VALUE' => $caption_no_value, // no default value - only in output
            'SHOW_NO_VALUE' => $show_no_value, // no default value - only in output
        ];
    }

    /**
     * @param array $arProperty
     * @param array $strHTMLControlName
     * @param array $arPropertyFields
     *
     * @return string
     */
    public static function GetSettingsHTML(array $arProperty, array $strHTMLControlName, array &$arPropertyFields) : string
    {
        $result = '';
        $userProperties = $arProperty['USER_TYPE_SETTINGS'];

        $value = $userProperties['MODULE'];
        $result .= '
		<tr>
			<td class="adm-detail-valign-top">'.'Модуль'.':</td>
			<td>
				<input type="text" name="'.$strHTMLControlName['NAME'].'[MODULE]" value="'.$value.'">
			</td>
		</tr>
		';

        $value = $userProperties['ORM_CLASS'];
        $result .= '
		<tr>
			<td class="adm-detail-valign-top">'.'ORM класс'.':</td>
			<td>
				<input type="text" name="'.$strHTMLControlName['NAME'].'[ORM_CLASS]" value="'.$value.'">
			</td>
		</tr>
		';

        $value = '';
        if ($userProperties['ORM_FIELD_CODE_ID']) {
            $value = trim($userProperties['ORM_FIELD_CODE_ID']);
        }

        $result .= '
		<tr>
			<td>Код поля для ID:</td>
			<td>
				<input type="text" name="'.$strHTMLControlName['NAME'].'[ORM_FIELD_CODE_ID]" size="10" value="'.htmlspecialcharsbx($value).'">
			</td>
		</tr>
		';

        $value = '';
        if ($userProperties['ORM_FIELD_CODE_NAME']) {
            $value = trim($userProperties['ORM_FIELD_CODE_NAME']);
        }

        $result .= '
		<tr>
			<td>Код поля для отображения:</td>
			<td>
				<input type="text" name="'.$strHTMLControlName['NAME'].'[ORM_FIELD_CODE_NAME]" size="10" value="'.htmlspecialcharsbx($value).'">
			</td>
		</tr>
		';

        $value = 'LIST';
        if ($userProperties['DISPLAY']) {
            $value = $userProperties['DISPLAY'];
        }

        $result .= '
		<tr>
			<td class="adm-detail-valign-top">'."Способ отображения".':</td>
			<td>
				<label><input type="radio" name="'.$strHTMLControlName['NAME'].'[DISPLAY]" value="LIST" '.("LIST" == $value ? 'checked="checked"' : '').'>'."Список".'</label><br>
				<label><input type="radio" name="'.$strHTMLControlName['NAME'].'[DISPLAY]" value="CHECKBOX" '.("CHECKBOX" == $value ? 'checked="checked"' : '').'>'."Чекбоксы".'</label><br>
				<label><input type="radio" disabled name="'.$strHTMLControlName['NAME'].'[DISPLAY]" value="UI" '.("UI" == $value ? 'checked="checked"' : '').'>'."UI".'</label><br>
			</td>
		</tr>
		';

        $value = 5;
        if ($userProperties['LIST_HEIGHT']) {
            $value = (int)$userProperties['LIST_HEIGHT'];
        }

        $result .= '
		<tr>
			<td>'.'Высота списка'.':</td>
			<td>
				<input type="text" name="'.$strHTMLControlName['NAME'].'[LIST_HEIGHT]" size="10" value="'.$value.'">
			</td>
		</tr>
		';

        $value = '';
        if ($userProperties['CAPTION_NO_VALUE']) {
            $value = trim($userProperties['CAPTION_NO_VALUE']);
        }

        $result .= '
		<tr>
			<td>Подпись при отсутствии значения:</td>
			<td>
				<input type="text" 
				    name="'.$strHTMLControlName['NAME'].'[CAPTION_NO_VALUE]" size="10" value="'.htmlspecialcharsbx($value).'">
			</td>
		</tr>
		';

        $value = '';
        if ($userProperties['SHOW_NO_VALUE']) {
            $value = trim($userProperties['SHOW_NO_VALUE']);
        }


        $result .= '
		<tr>
			<td>Показывать пустое значение для обязательного поля:</td>
			<td>
				<input type="hidden" name="' . $strHTMLControlName['NAME'] . '[SHOW_NO_VALUE]" value="N" />
				<label><input type="checkbox" 
				    name="' . $strHTMLControlName['NAME'] . '[SHOW_NO_VALUE]" value="Y" '.($value === 'N' ? '' : ' checked="checked"').' />Да</label>
			</td>
		</tr>
		';

        return $result;
    }

    /**
     * @param array $arProperty
     * @param mixed $propertyValue
     * @param mixed $propertyFormCfg
     *
     * @return string
     */
    public function GetEditFormHTML(array $arProperty, $propertyValue, $propertyFormCfg) : string
    {
        $enum = static::getEnumList($arProperty);

        if (empty($enum)) {
            return '';
        }

        $propertyFactory = new Factory();
        $property = $propertyFactory->getProperty($arProperty);

        return $property->getEditHTML($enum, $propertyValue, $propertyFormCfg);
    }

    /**
     * @param array $arUserField
     * @param array $arHtmlControl
     *
     * @return string
     */
    public static function GetFilterHTML(array $arUserField, array $arHtmlControl): string
    {
        if (!is_array($arHtmlControl['VALUE'])) {
            $arHtmlControl['VALUE'] = [];
        }

        $enum = static::getEnumList($arUserField);
        if (empty($enum)) {
            return '';
        }

        $size = ' size="'.$arUserField['SETTINGS']['LIST_HEIGHT'].'"';
        if ($arUserField['SETTINGS']['LIST_HEIGHT'] < 5) {
            $size = ' size="5"';
        }

        $result = '<select multiple name="'.$arHtmlControl['NAME'].'[]"'.$size.'>';
        $result .= '<option value=""'.(!$arHtmlControl["VALUE"] ? ' selected' : '').'>
        Главное</option>';
        foreach ($enum as $idEnum => $enumName) {
            $result .= '<option value="'.$idEnum.'"'.(in_array($idEnum,
                    $arHtmlControl['VALUE']) ? ' selected' : '').'>'.$enumName.'</option>';
        }

        $result .= '</select>';

        return $result;
    }

    /**
     * @param array $arUserField
     * @param array $arHtmlControl
     *
     * @return array
     */
    public function GetFilterData(array $arUserField, array $arHtmlControl) : array
    {
        $items = static::getEnumList($arUserField);

        return [
            'id' => $arHtmlControl['ID'],
            'NAME' => $arHtmlControl['NAME'],
            'type' => 'list',
            'items' => $items,
            'params' => ['multiple' => 'Y'],
            'filterable' => '',
        ];
    }

    /**
     * @param array $arUserField
     * @param array $arParams
     *
     * @return array
     */
    protected static function getEnumList(array &$arUserField, array $arParams = []) : array
    {
        $enum = [];
        $showNoValue = $arUserField['MANDATORY'] !== 'Y'
            || $arUserField['SETTINGS']['SHOW_NO_VALUE'] !== 'N'
            || (isset($arParams['SHOW_NO_VALUE']) && $arParams['SHOW_NO_VALUE'] === true);

        if ($showNoValue
            && ($arUserField['SETTINGS']['DISPLAY'] !== 'CHECKBOX' || $arUserField['MULTIPLE'] !== 'Y')
        ) {
            /** @psalm-suppress UndefinedFunction */
            $enum = [null => htmlspecialcharsbx(static::getEmptyCaption($arUserField))];
        }

        $ormFieldCodeId = $arUserField['USER_TYPE_SETTINGS']['ORM_FIELD_CODE_ID'];
        $ormFieldCodeName = $arUserField['USER_TYPE_SETTINGS']['ORM_FIELD_CODE_NAME'];

        try {
            Loader::includeModule($arUserField['USER_TYPE_SETTINGS']['MODULE']);
        } catch (Exception $e) {
        }

        if (empty($arUserField['USER_TYPE_SETTINGS']['ORM_CLASS'])) {
            return $enum;
        }

        /** @var Result $rsEnum */
        $rsEnum = call_user_func_array(
            [$arUserField['USER_TYPE_SETTINGS']['ORM_CLASS'], 'getList'],
            []
        );

        while ($arEnum = $rsEnum->fetch()) {
            $enum[$arEnum[$ormFieldCodeId]] = $arEnum[$ormFieldCodeName];
        }

        $arUserField['USER_TYPE']['FIELDS'] = $enum;

        return $enum;
    }

    /**
     * @param array $arUserField
     *
     * @return mixed|string
     */
    protected static function getEmptyCaption(array $arUserField)
    {
        return $arUserField['SETTINGS']['CAPTION_NO_VALUE'] !== ''
            ? $arUserField['SETTINGS']['CAPTION_NO_VALUE']
            : 'не задано';
    }

    /**
     * @param array $enum
     * @param mixed $propertyValue
     * @param array $propertyFormCfg
     *
     * @return string
     */
    protected function getEditHTML(array $enum, $propertyValue, array $propertyFormCfg) : string
    {
        return '';
    }
}