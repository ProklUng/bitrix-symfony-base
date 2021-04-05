<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties;


use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction\IblockPropertyTypeBase;

/**
 * Class YesNoType
 *
 * Тип свойства "Да/Нет", который также выводит состояние "не задано", когда в базе данных для элемента инфоблока
 * значение свойства ещё не определено. Это должно помочь избежать ситуации, когда в админке отображалось бы "Нет", а
 * при фильтрации по значению 0 требуемые элементы не попадают в выборку.
 *
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties
 */
class YesNoType extends IblockPropertyTypeBase
{
    private const CHECKED_ATTR = ' checked="checked" ';
    private const SELECTED_ATTR = ' selected="selected" ';
    private const VALUE_YES = true;
    private const VALUE_NO = false;

    /**
     * @inheritdoc
     */
    public function getPropertyType()
    {
        return self::PROPERTY_TYPE_NUMBER;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'Признак "Да/Нет"';
    }

    /**
     * @inheritdoc
     */
    public function getCallbacksMapping()
    {
        return [
            'GetAdminListViewHTML' => [$this, 'getAdminListViewHTML'],
            'GetPropertyFieldHtml' => [$this, 'getPropertyFieldHtml'],
            'ConvertToDB'          => [$this, 'convertToDB'],
            'ConvertFromDB'        => [$this, 'convertFromDB'],
            'GetAdminFilterHTML'   => [$this, 'getAdminFilterHTML'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAdminListViewHTML(array $property, array $value, array $control)
    {
        return $this->getHumanValueRepresentation($value);
    }

    /**
     * @inheritdoc
     */
    public function getPropertyFieldHtml(array $property, array $value, array $control)
    {
        $isYes = self::VALUE_YES === $value['VALUE'];
        $checked = $isYes ? self::CHECKED_ATTR : '';

        $return =
            '<input type="hidden" name="' . $control['VALUE'] . '" value="0" />'
            . '<input'
            . $checked
            . ' type="checkbox" name="'
            . $control['VALUE']
            . '" id="'
            . $control['VALUE']
            . '" value="1" />';

        if ($this->getControlMode($control) !== self::CONTROL_MODE_EDIT_FORM) {
            $return .= '<br><small>Текущее значение: ' . $this->getHumanValueRepresentation($value) . '</small>';
        }

        if (
            $this->getControlMode($control) !== self::CONTROL_MODE_EDIT_FORM
            && $property['WITH_DESCRIPTION'] == 'Y'
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
    public function getAdminFilterHTML(array $property, array $control)
    {
        $curValue = null;
        if (isset($GLOBALS[$control['VALUE']])) {
            $curValue = (int)$GLOBALS[$control['VALUE']];
        }

        /** @noinspection HtmlUnknownAttribute */
        $html =
            '<select name="%s">'
            . '<option value="" >(любой)</option>'
            . '<option value="1" %s >да</option>'
            . '<option value="0" %s >нет</option>'
            . '</select>';

        return sprintf(
            $html,
            $control['VALUE'],
            1 === $curValue ? self::SELECTED_ATTR : '',
            0 === $curValue ? self::SELECTED_ATTR : ''
        );
    }

    /**
     * @inheritdoc
     */
    public function convertToDB(array $property, array $value)
    {
        /**
         * При сохранении в БД неоднозначность преобразуется к "Нет".
         */
        $value['VALUE'] = self::normalize($value);

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function convertFromDB(array $property, array $value)
    {
        /**
         * При чтении из БД возможно также неоднозначное состояние "Не задано".
         * При этом при запросе через CIBlockElement::GetList в $value['VALUE'] будет '1.0000',
         * поэтому строгое равенство использовать нельзя.
         */
        if ('1' == $value['VALUE']) {
            $value['VALUE'] = true;
        } elseif ('0' == $value['VALUE']) {
            $value['VALUE'] = false;
        } else {
            $value['VALUE'] = null;
        }

        return $value;
    }

    /**
     * @param array $value
     *
     * @return int
     */
    public static function normalize(array $value)
    {
        return (int)$value['VALUE'];
    }

    /**
     * Возвращает человеко-понятное представление значения.
     *
     * @param array $value
     *
     * @return string
     */
    protected function getHumanValueRepresentation(array $value)
    {
        if (self::VALUE_YES === $value['VALUE']) {
            return 'да';
        } elseif (self::VALUE_NO === $value['VALUE']) {
            return 'нет';
        }

        return 'не задано';
    }

}
