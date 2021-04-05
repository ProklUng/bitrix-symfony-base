<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\EnumProperty;

/**
 * Class Select
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\EnumProperty
 */
class Select extends Base
{
    /**
     * @param array $enum
     * @param mixed $propertyValue
     * @param array $propertyFormCfg
     *
     * @return string
     */
    public function getEditHTML(array $enum, $propertyValue, array $propertyFormCfg) : string
    {
        $arProperty = $this->property;
        $bWasSelect = false;
        $result2 = '';

        foreach ($enum as $idEnum => $valEnum) {
            $bSelected = $propertyValue['VALUE'] == $idEnum;
            $bWasSelect = $bWasSelect || $bSelected;
            $result2 .= '<option value="' . $idEnum . '"' . ($bSelected ? ' selected' : '') . '>' . $valEnum . '</option>';
        }

        if (!array_key_exists('SETTINGS', $arProperty)) {
            $arProperty['SETTINGS'] = [];
        }

        if ($arProperty['SETTINGS']['LIST_HEIGHT'] > 1) {
            $size = ' size="' . $arProperty['SETTINGS']['LIST_HEIGHT'] . '"';
        } else {
            $propertyValue['VALIGN'] = 'middle';
            $size = '';
        }

        $result = '<select name="' . $propertyFormCfg['VALUE'] . '"' . $size . '>';

        $result .= $result2;
        $result .= '</select>';

        return $result;
    }
}
