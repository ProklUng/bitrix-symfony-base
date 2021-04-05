<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties;

use CIBlockElement;
use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction\IblockPropertyTypeNativeInterface;

/**
 * Class ListElementWithDescription
 * Свойство Привязка элемента с описанием.
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties
 *
 * @since 10.02.2021
 */
class ListElementWithDescription implements IblockPropertyTypeNativeInterface
{
    /**
     * @const string USER_TYPE
     */
    public const USER_TYPE = 'multiBindProp';

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        /** @psalm-suppress UndefinedFunction */
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
            'PROPERTY_TYPE' => 'E',
            "USER_TYPE" => self::USER_TYPE,
            'DESCRIPTION' => 'Привязка к элементу с описанием',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
        ];
    }

    /**
     * @param array $arProperty
     * @param array $arValue
     * @param array $strHTMLControlName
     *
     * @return string
     */
    public static function GetPropertyFieldHtml(array $arProperty, array $arValue, array $strHTMLControlName) : string
    {
        // Описание превращаем обратно в массив ( в шаблоне также )
        $arValue['DESCRIPTION'] = unserialize($arValue['DESCRIPTION']);
        $key = '';
        $arItem = [];

        if ((int)$arValue['VALUE'] > 0) {
            $arFilter = [
                'ID' => (int)$arValue['VALUE'],
                'IBLOCK_ID' => $arProperty['LINK_IBLOCK_ID'],
            ];

            $rsItem = CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'IBLOCK_ID', 'NAME']);
            $arItem = $rsItem->GetNext();
        }

        return '
      <table border="0" cellspacing="0" cellpadding="0" width="100%" class="internal">
        <tbody>
          <tr class="heading">
            <td>Элемент</td>
            <td>Вкладка</td>
          </tr>
          <tr>
            <td align="center" width="50%">
              <input name="'.$strHTMLControlName['VALUE'].'" id="'.$strHTMLControlName['VALUE'].'" value="'.htmlspecialcharsex($arValue['VALUE']).'" size="5" type="text">
              <input type="button" value="Выбрать" onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&IBLOCK_ID='.$arProperty["LINK_IBLOCK_ID"].'&n='.$strHTMLControlName['VALUE'].'\', 600, 500);">
              <br><span id="sp_'.md5($strHTMLControlName['VALUE']).'_'.$key.'" >'.$arItem["NAME"].'</span>
            </td>
            <td align="center">
              <input type="text" id="meas" name="'.$strHTMLControlName['DESCRIPTION'].'[0]" value="'.htmlspecialcharsex($arValue['DESCRIPTION'][0]).'" />
            </td>
          </tr>
        </tbody>
      </table>
    ';
    }

    /**
     * @param mixed $arProperty
     * @param mixed $arValue
     *
     * @return mixed
     */
    public static function ConvertToDB($arProperty, array $arValue)
    {
        //Если значение или описание массив, то сериализуем
        if (is_array($arValue) && array_key_exists('VALUE', $arValue) && !empty($arValue['VALUE'])) {
            $arValue['VALUE'] = serialize($arValue['VALUE']);
            $arValue['DESCRIPTION'] = serialize($arValue['DESCRIPTION']);
        }

        return $arValue;
    }

    /**
     * @param mixed $arProperty
     * @param mixed $arValue
     *
     * @return array|mixed
     */
    public function ConvertFromDB($arProperty, $arValue)
    {
        if (is_array($arValue) && array_key_exists('VALUE', $arValue) && !empty($arValue['VALUE'])) {
            $arValue['VALUE'] = unserialize($arValue['VALUE']);
        }

        return $arValue;
    }
}