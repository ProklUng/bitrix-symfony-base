<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties;

use Illuminate\Contracts\Validation\Rule;
use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction\IblockPropertyTypeBase;

/**
 * Class Email
 * Кастомное поле типа EMAIL.
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties
 *
 * @since 17.10.2020
 */
class Email extends IblockPropertyTypeBase
{
    /**
     * @var Rule $emailValidator
     */
    private $emailValidator;

    /**
     * Email constructor.
     *
     * @param Rule $emailValidator Валидатор email.
     */
    public function __construct(Rule $emailValidator)
    {
        $this->emailValidator = $emailValidator;
    }

    /**
     * @inheritdoc
     */
    public function getPropertyType()
    {
        return self::PROPERTY_TYPE_STRING;
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return 'Email';
    }

    /**
     * @inheritDoc
     */
    public function getCallbacksMapping()
    {
        return [
            'GetAdminListViewHTML' => [$this, 'getAdminListViewHTML'],
            'GetPropertyFieldHtml' => [$this, 'getPropertyFieldHtml'],
            'ConvertToDB'          => [$this, 'convertToDB'],
            'ConvertFromDB'        => [$this, 'convertFromDB'],
            'CheckFields'          => [$this, 'checkFields'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAdminListViewHTML(array $property, array $value, array $control)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getPropertyFieldHtml(array $property, array $value, array $control)
    {
        $return = '<input type="text" name="' . $control['VALUE'] . '" value="' . $value['VALUE'] . '" />';

        if (
            $this->getControlMode($control) !== self::CONTROL_MODE_EDIT_FORM
            && $property['WITH_DESCRIPTION'] === 'Y'
        ) {
            $return .= '<div><input type="text" size="'
                . $property['COL_COUNT']
                . '" name="'
                . $control['DESCRIPTION']
                . '" value="'
                . htmlspecialchars($value['DESCRIPTION'])
                . '" /></div>';
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function convertToDB(array $property, array $value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function convertFromDB(array $property, array $value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function checkFields(array $property, array $value)
    {
        if (!$this->emailValidator->passes('', $value['VALUE'])) {
            return ['Email не валиден'];
        }

        return [];
    }
}
