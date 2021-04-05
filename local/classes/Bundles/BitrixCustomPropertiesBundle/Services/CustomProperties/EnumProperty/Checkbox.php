<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\EnumProperty;

/**
 * Class Checkbox
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\EnumProperty
 */
class Checkbox extends Base
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
        $bWasSelect = false;
        $result = '';

        foreach ($enum as $idEnum => $valEnum) {
            $bSelected = $propertyValue['VALUE'] == $idEnum;
            $bWasSelect = $bWasSelect || $bSelected;
            $result .= '<label>
                            <input type="radio" value="'.$idEnum.'" name="'.$propertyFormCfg['VALUE'].'"'.($bSelected ? ' checked' : '').'>'.$valEnum.'</label><br>';
        }

        return $result;
    }
}
