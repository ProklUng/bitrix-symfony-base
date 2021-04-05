<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\Abstracts;

/**
 * Class AbstractUserTypeProperty
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\Abstracts
 *
 * @since 09.02.2021
 */
class AbstractUserTypeProperty
{
    /**
     * Массив описания собственного типа свойств.
     *
     * @return array
     */
    public function GetUserTypeDescription() : array
    {
        return [];
    }

    /**
     * Обязательный метод для определения типа поля таблицы в БД при создании свойства.
     *
     * @param mixed $arUserField Пользовательское поле.
     *
     * @return string
     */
    public function GetDBColumnType($arUserField)
    {
        global $DB;
        switch (strtolower($DB->type)) {
            case "mysql":
                return "int(18)";
            case "oracle":
                return "number(18)";
            case "mssql":
                return "int";
        }

        return "int";
    }


    /**
     * Получить HTML формы для редактирования свойства.
     *
     * @param array $arUserField
     * @param array $arHtmlControl
     *
     * @return string
     */
    public function GetEditFormHTML(array $arUserField, array $arHtmlControl) : string
    {
        if (($arUserField['ENTITY_VALUE_ID'] < 1) && strlen($arUserField['SETTINGS']['DEFAULT_VALUE']) > 0) {
            $arHtmlControl['VALUE'] = (int)$arUserField['SETTINGS']['DEFAULT_VALUE'];
        }

        $rsEnum = call_user_func_array(
            [$arUserField['USER_TYPE']['CLASS_NAME'], 'GetList'],
            [$arUserField]
        );

        if (!$rsEnum) {
            return '';
        }

        $bWasSelect = false;
        $result2 = '';
        foreach ($rsEnum as $arEnum) {
            $bSelected = (
                ($arHtmlControl['VALUE'] == $arEnum['ID']) ||
                ($arUserField['ENTITY_VALUE_ID'] <= 0 && $arEnum['DEF'] == 'Y') //Можно сделать логику для дефолтного значения
            );
            $bWasSelect = $bWasSelect || $bSelected;
            $result2 .= '<option value="'.$arEnum['ID'].'"'.($bSelected ? ' selected' : '').'>'.$arEnum['VALUE'].'</option>';
        }

        if ($arUserField['SETTINGS']['LIST_HEIGHT'] > 1) {
            $size = ' size="'.$arUserField['SETTINGS']['LIST_HEIGHT'].'"';
        } else {
            $arHtmlControl['VALIGN'] = 'middle';
            $size = '';
        }

        $result = '<select name="'.$arHtmlControl['NAME'].'"'.$size.($arUserField['EDIT_IN_LIST'] != 'Y' ? ' disabled="disabled" ' : '').'>';
        if ($arUserField["MANDATORY"] != 'Y') {
            /** @psalm-suppress UndefinedFunction */
            $result .= '<option value=""'.(!$bWasSelect ? ' selected' : '').'>'.htmlspecialcharsbx(
                    self::getEmptyCaption($arUserField)
                ).'</option>';
        }

        $result .= $result2;
        $result .= '</select>';

        return $result;
    }

    /**
     * Получить HTML формы для редактирования МНОЖЕСТВЕННОГО свойства.
     *
     * @param array $arUserField
     * @param array $arHtmlControl
     *
     * @return string
     */
    public function GetEditFormHTMLMulty(array $arUserField, array $arHtmlControl) : string
    {
        $rsEnum = call_user_func_array([$arUserField['USER_TYPE']['CLASS_NAME'], 'GetList'], [$arUserField]);

        if (!$rsEnum) {
            return '';
        }

        $result = '<select multiple name="'.$arHtmlControl['NAME'].'" size="'.$arUserField['SETTINGS']['LIST_HEIGHT'].'"'.($arUserField["EDIT_IN_LIST"] != 'Y' ? ' disabled="disabled" ' : '').'>';

        if ($arUserField['MANDATORY'] !== 'Y') {
            /** @psalm-suppress UndefinedFunction */
            $result .= '<option value=""'.(!$arHtmlControl['VALUE'] ? ' selected' : '').'>'.htmlspecialcharsbx(
                    self::getEmptyCaption($arUserField)
                ).'</option>';
        }

        foreach ($rsEnum as $arEnum) {
            $bSelected = (
                (in_array($arEnum['ID'], $arHtmlControl['VALUE'])) ||
                ($arUserField['ENTITY_VALUE_ID'] <= 0 && $arEnum['DEF'] === 'Y') //Можно сделать логику для дефолтного значения
            );
            $result .= '<option value="'.$arEnum["ID"].'"'.($bSelected ? ' selected' : '').'>'.$arEnum['VALUE'].'</option>';
        }
        $result .= '</select>';

        return $result;
    }

    /**
     * Получаем HTML для списка элементов в админке.
     *
     * @param array $arUserField
     * @param array $arHtmlControl
     *
     * @return mixed|string
     */
    public function GetAdminListViewHTML(array $arUserField, array $arHtmlControl)
    {
        static $cache = [];
        $empty_caption = '&nbsp;';

        if (!array_key_exists($arHtmlControl['VALUE'], $cache)) {
            $rsEnum = call_user_func_array([$arUserField['USER_TYPE']['CLASS_NAME'], 'GetList'], [$arUserField]);
            if (!$rsEnum) {
                return $empty_caption;
            }

            foreach ($rsEnum as $arEnum) {
                $cache[$arEnum["ID"]] = $arEnum['VALUE'];
            }
        }

        if (!array_key_exists($arHtmlControl['VALUE'], $cache)) {
            $cache[$arHtmlControl['VALUE']] = $empty_caption;
        }

        return $cache[$arHtmlControl['VALUE']];
    }

    /**
     * Получить HTML для редактирования свойства в списке админ-панели.
     *
     * @param array $arUserField
     * @param array $arHtmlControl
     *
     * @return string
     */
    public function GetAdminListEditHTML(array $arUserField, array $arHtmlControl) : string
    {
        $rsEnum = call_user_func_array(
            [$arUserField["USER_TYPE"]["CLASS_NAME"], "getList"],
            [$arUserField]
        );

        if (!$rsEnum) {
            return '';
        }

        $size = '';
        if ($arUserField['SETTINGS']['LIST_HEIGHT'] > 1) {
            $size = ' size="'.$arUserField['SETTINGS']['LIST_HEIGHT'].'"';
        }

        $result = '<select name="'.$arHtmlControl['NAME'].'"'.$size.($arUserField["EDIT_IN_LIST"] != 'Y' ? ' disabled="disabled" ' : '').'>';
        if ($arUserField['MANDATORY'] !== 'Y') {
            /** @psalm-suppress UndefinedFunction */
            $result .= '<option value=""'.(!$arHtmlControl['VALUE'] ? ' selected' : '').'>'.htmlspecialcharsbx(
                    self::getEmptyCaption($arUserField)
                ).'</option>';
        }
        foreach ($rsEnum as $key => $arEnum) {
            $result .= '<option value="'.$arEnum["ID"].'"'.($arHtmlControl['VALUE'] == $arEnum["ID"] ? ' selected' : '').'>'.$arEnum['VALUE'].'</option>';
        }
        $result .= '</select>';

        return $result;
    }

    /**
     * Получить HTML для редактирования множественного свойства в списке админ-панели.
     *
     * @param array $arUserField
     * @param array $arHtmlControl
     *
     * @return string
     */
    public function GetAdminListEditHTMLMulty(array $arUserField, array $arHtmlControl): string
    {
        if (!is_array($arHtmlControl['VALUE'])) {
            $arHtmlControl['VALUE'] = [];
        }

        $rsEnum = call_user_func_array(
            [$arUserField["USER_TYPE"]["CLASS_NAME"], "getList"],
            [$arUserField]
        );

        if (!$rsEnum) {
            return '';
        }

        $result = '<select multiple name="' . $arHtmlControl['NAME'] . '" size="'.$arUserField['SETTINGS']['LIST_HEIGHT'].'"'.($arUserField["EDIT_IN_LIST"] != 'Y' ? ' disabled="disabled" ' : '').'>';
        if ($arUserField['MANDATORY'] !== 'Y') {
            /** @psalm-suppress UndefinedFunction */
            $result .= '<option value=""'.(!$arHtmlControl['VALUE'] ? ' selected' : '').'>'.htmlspecialcharsbx(
                    self::getEmptyCaption($arUserField)
                ).'</option>';
        }
        foreach ($rsEnum as $arEnum) {
            $result .= '<option value="'.$arEnum["ID"].'"'.(in_array(
                    $arEnum["ID"],
                    $arHtmlControl['VALUE']
                ) ? ' selected' : '').'>'.$arEnum['VALUE'].'</option>';
        }

        $result .= '</select>';

        return $result;
    }

    /**
     * Получаем HTML блок для фильтрации списка эдементов по этому свойству.
     *
     * @param array $arUserField
     * @param array $arHtmlControl
     *
     * @return string
     */
    public function GetFilterHTML(array $arUserField, array $arHtmlControl): string
    {
        if (!is_array($arHtmlControl['VALUE'])) {
            $arHtmlControl['VALUE'] = [];
        }

        $rsEnum = call_user_func_array(
            [$arUserField["USER_TYPE"]["CLASS_NAME"], "getList"],
            [$arUserField]
        );

        if (!$rsEnum) {
            return '';
        }

        $size = ' size="'.$arUserField['SETTINGS']['LIST_HEIGHT'].'"';
        if ($arUserField['SETTINGS']['LIST_HEIGHT'] < 5) {
            $size = ' size="5"';
        }

        $result = '<select multiple name="'.$arHtmlControl['NAME'].'[]"'.$size.'>';
        $result .= '<option value=""'.(!$arHtmlControl['VALUE'] ? ' selected' : '').'>Не установлено</option>';
        foreach ($rsEnum as $key => $arEnum) {
            $result .= '<option value="'.$arEnum["ID"].'"'.(in_array(
                    $arEnum["ID"],
                    $arHtmlControl['VALUE']
                ) ? ' selected' : '').'>'.$arEnum['VALUE'].'</option>';
        }
        $result .= '</select>';

        return $result;
    }

    /**
     * Получаем текст для пустого значения свойства.
     *
     * @param array $arUserField
     *
     * @return mixed|string|string[]
     */
    protected static function getEmptyCaption(array $arUserField)
    {
        return 'Не установлено';
    }
}