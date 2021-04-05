<?php

class CASDiblockTools
{

    public static $arNotExport = [
        'ID',
        'TIMESTAMP_X',
        'IBLOCK_ID',
        'TMP_ID',
        'EXTERNAL_ID',
        'PROPERTY_ID',
        'PROPERTY_NAME',
        'PROPERTY_SORT',
    ];

    /**
     * @param integer $iblockId
     * @param mixed   $arWhat
     *
     * @return string
     */
    public static function ExportSettingsToXML(int $iblockId, $arWhat): string
    {
        $xml = '';
        if ($iblockId > 0 && is_array($arWhat) && !empty($arWhat) && in_array('forms', $arWhat, true)) {
            $formElement = CUserOptions::GetOption('form', 'form_element_'.$iblockId, true);
            $formSection = CUserOptions::GetOption('form', 'form_section_'.$iblockId, true);

            $xml .= '<form_element>';
            $xml .= '<![CDATA['.array_pop($formElement).']]>';
            $xml .= '</form_element>'."\n";
            $xml .= '<form_section>';
            $xml .= '<![CDATA['.array_pop($formSection).']]>';
            $xml .= '</form_section>'."\n";
        }

        return $xml;
    }

    /**
     * @param integer $iblockId
     * @param array   $arOnlyID
     *
     * @return string
     */
    public static function ExportPropsToXML(int $iblockId, array $arOnlyID = [])
    {
        $xml = '';
        if (empty($arOnlyID)) {
            $arOnlyID = $_REQUEST['p'];
        }
        if ($iblockId > 0 && CModule::IncludeModule('iblock')) {
            $xml .= "\t".'<props>'."\n";
            $arExported = [];
            $arCData = ['NAME', 'DEFAULT_VALUE', 'XML_ID', 'FILE_TYPE', 'USER_TYPE_SETTINGS', 'HINT', 'VALUE'];
            $rsProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId]);
            while ($arProp = $rsProp->Fetch()) {
                if (!empty($arOnlyID) && !isset($arOnlyID[$arProp['ID']])) {
                    continue;
                }
                $arExported[] = $arProp['CODE'];
                $xml .= "\t\t".'<prop>'."\n";
                foreach ($arProp as $k => $v) {
                    if ($k == 'ID') {
                        $k = 'OLD_ID';
                    }
                    if (in_array($k, self::$arNotExport, true)) {
                        continue;
                    }
                    if (in_array($k, $arCData, true) && strlen(trim($v))) {
                        $v = '<![CDATA['.$v.']]>';
                    }
                    $xml .= "\t\t\t".'<'.strtolower($k).'>'.$v.'</'.strtolower($k).'>'."\n";
                }
                $xml .= "\t\t".'</prop>'."\n";
            }
            $xml .= "\t".'</props>'."\n";
            $xml .= "\t".'<enums>'."\n";
            $rsProp = CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $iblockId]);
            while ($arProp = $rsProp->Fetch()) {
                if (!in_array($arProp['PROPERTY_CODE'], $arExported)) {
                    continue;
                }
                $xml .= "\t\t".'<enum>'."\n";
                foreach ($arProp as $k => $v) {
                    if (in_array($k, self::$arNotExport, true)) {
                        continue;
                    }
                    if (in_array($k, $arCData, true) && trim($v) !== '') {
                        $v = '<![CDATA['.$v.']]>';;
                    }
                    $xml .= "\t\t\t".'<'.strtolower($k).'>'.$v.'</'.strtolower($k).'>'."\n";
                }
                $xml .= "\t\t".'</enum>'."\n";
            }
            $xml .= "\t".'</enums>'."\n";
        }

        return $xml;
    }

    public static function ImportFormsFromXML($BID, $xmlPath, $arOldNewID)
    {
        if ($BID && file_exists($xmlPath) && CModule::IncludeModule('iblock')) {
            require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/xml.php');
            $xml = new CDataXML();
            if ($xml->Load($xmlPath)) {
                if ($node = $xml->SelectNodes('/asd_iblock_props/form_element/')) {
                    $tabs = $node->textContent();
                    foreach ($arOldNewID as $old => $new) {
                        $tabs = str_replace('--PROPERTY_'.$old.'--', '--PROPERTY_'.$new.'--', $tabs);
                    }
                    $arOptions = [
                        [
                            'd' => 'Y',
                            'c' => 'form',
                            'n' => 'form_element_'.$BID,
                            'v' => ['tabs' => $tabs],
                        ],
                    ];
                    CUserOptions::SetOptionsFromArray($arOptions);
                }
                if ($node = $xml->SelectNodes('/asd_iblock_props/form_section/')) {
                    $tabs = $node->textContent();
                    $arOptions = [
                        [
                            'd' => 'Y',
                            'c' => 'form',
                            'n' => 'form_section_'.$BID,
                            'v' => ['tabs' => $tabs],
                        ],
                    ];
                    CUserOptions::SetOptionsFromArray($arOptions);
                }
            }
        }
    }

    public static function ImportPropsFromXML($BID, $xmlPath, &$arOldNewID)
    {
        if (file_exists($xmlPath) && $BID && CModule::IncludeModule('iblock')) {

            require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/xml.php');

            $arExistProps = [];
            $rsProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $BID]);
            while ($arProp = $rsProp->Fetch()) {
                $arExistProps[$arProp['CODE']] = $arProp;
            }

            $arExistEnums = [];
            $rsEnum = CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $BID]);
            while ($arEnum = $rsEnum->Fetch()) {
                $arExistEnums[$arEnum['PROPERTY_ID'].'_'.$arEnum['XML_ID']] = $arEnum;
            }

            $arOldNewID = [];
            $xml = new CDataXML();
            $ep = new CIBlockProperty();
            $en = new CIBlockPropertyEnum();
            if ($xml->Load($xmlPath)) {
                if ($node = $xml->SelectNodes('/asd_iblock_props/props/')) {
                    foreach ($node->children() as $child) {
                        $arChild = $child->__toArray();

                        $arProp = array_pop($arChild);
                        $arFields = ['IBLOCK_ID' => $BID];
                        foreach ($arProp as $code => $v) {
                            $arFields[strtoupper($code)] = isset($v[0]['#']['cdata-section']) && is_array($v[0]['#']['cdata-section']) ? $v[0]['#']['cdata-section'][0]['#'] : $v[0]['#'];
                        }
                        if (isset($arExistProps[$arFields['CODE']])) {
                            $arOldNewID[$arFields['OLD_ID']] = $arExistProps[$arFields['CODE']]['ID'];
                            $ep->Update($arExistProps[$arFields['CODE']]['ID'], $arFields);
                        } else {
                            $arOldNewID[$arFields['OLD_ID']] = $arFields['ID'] = $ep->Add($arFields);
                            $arExistProps[$arFields['CODE']] = $arFields;
                        }
                    }
                }
                if ($node = $xml->SelectNodes('/asd_iblock_props/enums/')) {
                    foreach ($node->children() as $child) {
                        $arChild = $child->__toArray();
                        $arProp = array_pop($arChild);
                        $arFields = ['IBLOCK_ID' => $BID];
                        foreach ($arProp as $code => $v) {
                            $arFields[strtoupper($code)] = isset($v[0]['#']['cdata-section']) && is_array($v[0]['#']['cdata-section']) ? $v[0]['#']['cdata-section'][0]['#'] : $v[0]['#'];
                        }
                        $arFields['PROPERTY_ID'] = $arExistProps[$arFields['PROPERTY_CODE']]['ID'];
                        if (isset($arExistEnums[$arFields['PROPERTY_ID'].'_'.$arFields['XML_ID']])) {
                            $en->Update($arExistEnums[$arFields['PROPERTY_ID'].'_'.$arFields['XML_ID']]['ID'],
                                $arFields);
                        } else {
                            $en->Add($arFields);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param integer $iblockId
     * @param string  $propertyCode
     *
     * @return array|mixed
     */
    public static function GetIBUF(int $iblockId, string $propertyCode = '')
    {
        global $USER_FIELD_MANAGER;
        $arReturn = [];
        $arUserFields = $USER_FIELD_MANAGER->GetUserFields(CASDiblock::$UF_IBLOCK, $iblockId, LANGUAGE_ID);
        foreach ($arUserFields as $FIELD_NAME => $arUserField) {
            if ($arUserField['USER_TYPE_ID'] === 'enumeration') {
                $arValue = [];
                $obEnum = new CUserFieldEnum();
                $rsSecEnum = $obEnum->GetList(
                    ['SORT' => 'ASC', 'ID' => 'ASC'],
                    ['USER_FIELD_ID' => $arUserField['ID'], 'ID' => $arUserField['VALUE']]
                );

                while ($arSecEnum = $rsSecEnum->Fetch()) {
                    $arValue[$arSecEnum['ID']] = $arSecEnum['VALUE'];
                }
                $arReturn[$FIELD_NAME] = $arValue;
            } else {
                $arReturn[$FIELD_NAME] = $arUserField['VALUE'];
            }
        }

        return $propertyCode === '' ? $arReturn : $arReturn[$propertyCode];
    }

    /**
     * @param integer $iblockId
     * @param array   $arFields
     *
     * @return void
     */
    public static function SetIBUF(int $iblockId, array $arFields): void
    {
        global $USER_FIELD_MANAGER;
        $USER_FIELD_MANAGER->Update(CASDiblock::$UF_IBLOCK, $iblockId, $arFields);
    }
}

class CASDIblockElementTools
{
    /**
     * Get seo field templates.
     *
     * @param int $iblockId Iblock ID.
     * @param int $elementId Element ID.
     * @param bool $getAll Get with inherited.
     * @return array
     */
    public static function getSeoFieldTemplates($iblockId, $elementId, $getAll = false)
    {
        $result = [];

        if (!CASDiblockVersion::checkMinVersion('14.0.0')) {
            return $result;
        }

        $getAll = ($getAll === true);
        $seoTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($iblockId, $elementId);
        $elementTemplates = $seoTemplates->findTemplates();
        if (empty($elementTemplates) || !is_array($elementTemplates)) {
            return $result;
        }
        foreach ($elementTemplates as &$fieldTemplate) {
            if (!$getAll && (!isset($fieldTemplate['INHERITED']) || $fieldTemplate['INHERITED'] !== 'N')) {
                continue;
            }
            $result[$fieldTemplate['CODE']] = $fieldTemplate['TEMPLATE'];
        }
        unset($fieldName, $data);

        return $result;
    }
}
