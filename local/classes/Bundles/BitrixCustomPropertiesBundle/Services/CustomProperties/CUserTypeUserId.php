<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties;

use CDBResult;
use CUser;
use CUserTypeManager;
use Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\Abstracts\AbstractUserTypeProperty;

/**
 * Class CUserTypeUserId
 * Кастомное UF свойство - привязка к пользователю.
 * @package Local\Bitrix\CustomProperties
 *
 * @since 09.02.2021
 */
class CUserTypeUserId extends AbstractUserTypeProperty
{
    /**
     * Массив описания собственного типа свойств.
     *
     * @return array
     */
    public function GetUserTypeDescription() : array
    {
        return [
            "USER_TYPE_ID" => 'custom_userid', //Уникальный идентификатор типа свойств
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => 'Привязка к пользователю',
            "BASE_TYPE" => CUserTypeManager::BASE_TYPE_INT,
        ];
    }

    /**
     * Получаем список значений
     *
     * @param $arUserField
     *
     * @return array|bool|CDBResult
     */
    public function GetList($arUserField)
    {
        $rsEnum = [];
        $by = 'id';
        $order = 'asc';
        $dbResultList = CUser::GetList($by, $order, []);
        while ($arResult = $dbResultList->Fetch()) {
            $rsEnum[] = [
                'ID' => $arResult['ID'],
                //Формат отображения значений
                'VALUE' => $arResult['NAME'].' '.$arResult['LAST_NAME'].' ('.$arResult['EMAIL'].')',
            ];
        }

        return $rsEnum;
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
        return $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] !== ''
            ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"]
            : 'Пользователь не выбран';
    }
}
