<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties;

use CGroup;
use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction\IblockPropertyTypeNativeInterface;

/**
 * Class GroupUser
 * Группа пользователей
 *
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties
 *
 * @since 10.02.2021
 */
class GroupUser implements IblockPropertyTypeNativeInterface
{
    /**
     * @inheritDoc
     */
    public function init() : void
    {
        /** @psalm-suppress UndefinedFunction */
        AddEventHandler(
            'block',
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
            "PROPERTY_TYPE" => "N",
            "USER_TYPE" => "USER_GROUP",
            "DESCRIPTION" => "Привязка к группе пользователей",
            "CheckFields" => [__CLASS__, "CheckFields"],
            "GetLength" => [__CLASS__, "GetLength"],
            "GetPropertyFieldHtml" => [__CLASS__, "GetPropertyFieldHtml"],
            "GetAdminListViewHTML" => [__CLASS__, "GetAdminListViewHTML"],
            "GetPublicViewHTML" => [__CLASS__, "GetPublicViewHTML"],
            "GetSearchContent" => [__CLASS__, "GetSearchContent"],
        ];
    }

    /**
     * @param array $arProperty
     * @param array $value
     *
     * @return array
     */
    public static function CheckFields(array $arProperty, array $value) : array
    {
        $arResult = [];
        if ((int)$value['VALUE']) {
            $by = 'c_sort';
            $order = 'asc';
            $groups = CGroup::GetList($by, $order, ['ACTIVE' => 'Y']);
            $bFound = false;
            while ($arGroup = $groups->Fetch()) {
                if ($arGroup['ID'] == $value['VALUE']) {
                    $bFound = true;
                }
            }
            if (!$bFound) {
                $arResult[] = 'Группа пользователей не найдена';
            }
        }

        return $arResult;
    }

    /**
     * @param mixed $arProperty
     * @param mixed $value
     *
     * @return integer
     */
    public static function GetLength($arProperty, $value) : int
    {
        if (is_array($value) && array_key_exists('VALUE', $value)) {
            return strLen(trim($value['VALUE']));
        }

        return 0;
    }

    /**
     * @param mixed $arProperty
     * @param array $value
     * @param array $strHTMLControlName
     *
     * @return string
     */
    public static function GetPropertyFieldHtml($arProperty, array $value, array $strHTMLControlName) : string
    {
        $rsGroups = CGroup::GetList($by, $order, ['ACTIVE' => 'Y']);
        ob_start();
        ?>
      <select name="<?= $strHTMLControlName['VALUE'] ?>">
        <option value="">Выбрать</option>
          <?php while ($arGroup = $rsGroups->Fetch()):?>
            <option
                value="<?= $arGroup['ID'] ?>"<?= ($value['VALUE'] == $arGroup['ID'] ? " selected=\"selected\"" : "") ?>>
              [<?= $arGroup['ID'] ?>] <?= $arGroup["NAME"] ?></option>
          <?endwhile; ?>
        ?>
      </select>
        <?php
        return (string)ob_get_clean();
    }

    /**
     * @param mixed $arProperty
     * @param array $value
     * @param mixed $strHTMLControlName
     *
     * @return string
     */
    public static function GetAdminListViewHTML($arProperty, array $value, $strHTMLControlName) : string
    {
        $group_id = (int)$value['VALUE'];
        if ($group_id) {
            $arGroup = CGroup::GetByID($value['VALUE'])->Fetch();

            return "[{$arGroup['ID']}] ".htmlspecialcharsex($arGroup["NAME"]);
        }

        return "&nbsp;";
    }

    /**
     * @param mixed $arProperty
     * @param array $value
     * @param mixed $strHTMLControlName
     *
     * @return string
     */
    public static function GetPublicViewHTML($arProperty, array $value, $strHTMLControlName) : string
    {
        $group_id = (int)$value['VALUE'];
        if ($group_id) {
            $arGroup = CGroup::GetByID($value['VALUE'])->Fetch();
            /** @psalm-suppress UndefinedFunction */
            return "[{$arGroup['ID']}] ".htmlspecialcharsex($arGroup["NAME"]);
        }

        return "&nbsp;";
    }

    /**
     * @param mixed $arProperty
     * @param array $value
     * @param mixed $strHTMLControlName
     *
     * @return string
     */
    public static function GetSearchContent($arProperty, array $value, $strHTMLControlName) : string
    {
        if (strlen($value['VALUE']) > 0) {
            return (string)$value['VALUE'];
        }

        return '';
    }
}