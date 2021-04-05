<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CForm;
use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction\IblockPropertyTypeBase;

/**
 * Class BitrixFormType
 * Привязка к форме
 *
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties
 */
class FormType extends IblockPropertyTypeBase
{
    /**
     * @var array
     */
    private static $formsList;

    /**
     * @inheritdoc
     */
    public function getPropertyType()
    {
        return self::PROPERTY_TYPE_STRING;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'Привязка к форме';
    }

    /**
     * @inheritdoc
     */
    public function getCallbacksMapping()
    {
        return [
            'GetAdminListViewHTML' => [$this, 'getAdminListViewHTML'],
            'GetPropertyFieldHtml' => [$this, 'getPropertyFieldHtml'],
            'GetAdminFilterHTML'   => [$this, 'getAdminFilterHTML'],
            'GetUIFilterProperty'  => [$this, 'getUIFilterProperty'],
        ];
    }

    /**
     * @inheritdoc
     *
     * @throws LoaderException
     */
    public function getAdminListViewHTML(array $property, array $value, array $control)
    {
        return self::getFormName($value['VALUE']);
    }

    /**
     * @inheritdoc
     * @throws LoaderException
     */
    public function getPropertyFieldHtml(array $property, array $value, array $control)
    {
        if (!Loader::includeModule('form')) {
            return $value['VALUE'];
        }

        return self::getFormFieldHtml($control['VALUE'], $value['VALUE']);
    }

    /**
     * @inheritdoc
     * @throws LoaderException
     */
    public function getUIFilterProperty(array $property, $controlName, array &$filter)
    {
        $filter["type"] = 'list';
        $filter["items"] = array_column(self::getFormList(), 'NAME', 'SID');
    }

    /**
     * @inheritdoc
     * @throws LoaderException
     */
    public function getAdminFilterHTML(array $property, array $control)
    {
        $curValue = '';
        if (isset($_REQUEST[$control['VALUE']])) {
            $curValue = $_REQUEST[$control['VALUE']];
        } elseif (isset($GLOBALS[$control['VALUE']])) {
            $curValue = $GLOBALS[$control['VALUE']];
        }

        return self::getFormFieldHtml($control['VALUE'], $curValue);
    }

    /**
     * @param string   $inputName
     * @param string   $selectedValue
     * @param boolean  $addEmpty
     *
     * @throws LoaderException
     * @return string
     */
    protected function getFormFieldHtml(string $inputName = '', string $selectedValue = '', bool $addEmpty = true) : string
    {
        $items = self::getFormList();
        $input = '<select style="max-width:250px;" name="' . $inputName . '">';

        $input .= ($addEmpty) ? '<option value="">нет</option>' : '';

        foreach ($items as $item) {
            $selected = ($item['SID'] == $selectedValue) ? 'selected="selected"' : '';
            $input .= '<option ' . $selected . ' value="' . $item['SID'] . '">' . $item['NAME'] . '</option>';
        }
        $input .= '</select>';

        return $input;
    }

    /**
     * @param string $sid Символьный код формы.
     *
     * @throws LoaderException
     * @return string
     */
    protected function getFormName(string $sid) : string
    {
        $sid = trim($sid);
        if ($sid === '') {
            return '';
        }

        $forms = self::getFormList();
        if (array_key_exists($sid, $forms) && array_key_exists('NAME', $forms[$sid])) {
            return trim($forms[$sid]['NAME']);
        }

        return trim($sid);
    }

    /**
     * @throws LoaderException
     * @return array
     */
    protected static function getFormList()
    {
        if (is_array(self::$formsList)) {
            return self::$formsList;
        }

        self::$formsList = [];
        if (Loader::includeModule('form')) {
            $by = 's_name';
            $order = 'asc';
            $isFiltered = null;

            $dbres = CForm::GetList($by, $order, [], $isFiltered);
            while ($item = $dbres->Fetch()) {
                if (!empty($item['SID'])) {
                    self::$formsList[$item['SID']] = $item;
                }
            }
        }

        return self::$formsList;
    }
}