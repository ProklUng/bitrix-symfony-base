<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\EnumProperty;

/**
 * Class Factory
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties\ORM\EnumProperty
 */
class Factory
{
    /**
     * @param array $property
     *
     * @return Checkbox|Select
     */
    public function getProperty(array $property)
    {
        switch ($property['USER_TYPE_SETTINGS']['DISPLAY']) {
            case 'CHECKBOX':
                return new Checkbox($property);
            case 'LIST':
            default:
                return new Select($property);
        }
    }
}
