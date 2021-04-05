<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction;

use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Exception\NotImplementedMethodException;
use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Exception\UnsupportedControlModeException;

/**
 * Class IblockPropertyTypeBase
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction
 */
abstract class IblockPropertyTypeBase implements IblockPropertyTypeInterface
{
    /**
     * Форма редактирования свойства.
     */
    public const CONTROL_MODE_EDIT_FORM = 'EDIT_FORM';

    /**
     * Форма заполнения свойства (из формы редактирования элемента).
     */
    public const CONTROL_MODE_FORM_FILL = 'FORM_FILL';

    /**
     * Отображение или редактирование в административной части в списке элементов инфоблока
     */
    public const CONTROL_MODE_IBLOCK_ELEMENT_ADMIN = 'iblock_element_admin';

    public const PROPERTY_TYPE_STRING = 'S';

    public const PROPERTY_TYPE_NUMBER = 'N';

    public const PROPERTY_TYPE_LIST = 'L';

    public const PROPERTY_TYPE_FILE = 'F';

    public const PROPERTY_TYPE_SECTION_LINK = 'G';

    public const PROPERTY_TYPE_ELEMENT_LINK = 'E';

    /**
     * @inheritdoc
     */
    public function init()
    {
        /** @psalm-suppress UndefinedFunction */
        return AddEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            [$this, 'getUserTypeDescription']
        );
    }

    /**
     * @inheritdoc
     */
    public function getUserTypeDescription()
    {
        return array_merge(
            [
                'PROPERTY_TYPE' => $this->getPropertyType(),
                'USER_TYPE'     => $this->getUserType(),
                'DESCRIPTION'   => $this->getDescription(),
            ],
            $this->getCallbacksMapping()
        );
    }

    /**
     * Возвращает какое свойство будет базовым для хранения значений пользовательского свойства, а также для фильтрации
     * и некоторых других действий. Возможные значения:
     *
     * S - строка
     * N - число с плавающей точкой
     * L - список значений
     * F - файл
     * G - привязка к разделам
     * E - привязка к элементам
     *
     * см. константы PROPERTY_TYPE_*
     *
     * @return string
     */
    abstract public function getPropertyType();

    /**
     * Возвращает краткое описание. Будет выведено в списке выбора типа свойства при редактировании информационного
     * блока.
     *
     * @return string
     */
    abstract public function getDescription();

    /**
     * Возвращает уникальный идентификатор пользовательского свойства.
     *
     * @return string
     */
    public function getUserType()
    {
        return static::class;
    }

    /**
     * Возвращает маппинг реализованных для данного типа свойства методов.
     *
     * Неуказанные методы будут заменены на стандартную реализацию из модуля инфоблоков. Если же метод указан, но не
     * имеет конкретной реализации, будет выброшено исключение NotImplementedMethodException()
     *
     * @return array
     * @see IblockPropertyTypeInterface::getUserTypeDescription
     *
     */
    abstract public function getCallbacksMapping();

    /**
     * Определяет в каком режиме отображается свойство.
     *
     * @param array $control
     *
     * @return string
     */
    protected function getControlMode(array $control)
    {
        if (!isset($control['MODE'])) {
            return '';
        }

        $possibleValues = [
            self::CONTROL_MODE_EDIT_FORM,
            self::CONTROL_MODE_FORM_FILL,
            self::CONTROL_MODE_IBLOCK_ELEMENT_ADMIN,
        ];

        if (in_array($control['MODE'], $possibleValues)) {
            return $control['MODE'];
        }

        return '';
    }

    /**
     * Возвращает name и key по имени поля ввода для значения.
     *
     * @param array $control
     *
     * @throws UnsupportedControlModeException
     * @return array
     */
    protected function parseNameAndKeyFromControl(array $control)
    {
        $matches = [];
        /**
         * 'PROP[111][69516][VALUE]' -> 'PROP[111]' && '69516'
         */
        if (
            self::CONTROL_MODE_FORM_FILL === $this->getControlMode($control)
            && preg_match('/^(PROP\[[^]]+])\[([^]]+)]/', $control['VALUE'], $matches) === 1
        ) {
            return [$matches[1], $matches[2]];
        }

        /**
         * 'FIELDS[7234][PROPERTY_111][69516][VALUE]' -> 'FIELDS[7234][PROPERTY_111]' && '69516'
         */
        if (
            self::CONTROL_MODE_IBLOCK_ELEMENT_ADMIN === $this->getControlMode($control)
            && preg_match('/^(FIELDS\[[^]]+]\[[^]]+])\[([^]]+)]/', $control['VALUE'], $matches) === 1
        ) {
            return [$matches[1], $matches[2]];
        }

        throw new UnsupportedControlModeException($control['MODE']);
    }

    /**
     * Возвращает массив $control, пригодный для передачи в self::getPropertyFieldHtml на основании $control,
     * переданного в self::getPropertyFieldHtmlMulty
     *
     * @param array $control
     * @param string $key
     *
     * @return array
     */
    protected function convertControlFromMultiToSingle(array $control, $key)
    {
        $singleControlName = $control;

        $singleControlName['VALUE'] = sprintf(
            '%s[%s][VALUE]',
            $control['VALUE'],
            $key
        );

        $singleControlName['DESCRIPTION'] = sprintf(
            '%s[%s][DESCRIPTION]',
            $control['VALUE'],
            $key
        );

        return $singleControlName;
    }

    /**
     * Возвращает JS код показа всплывающего окна для поиска элемента инфоблока, который можно, например, вставить в
     * аттрибут onclick
     *
     * @param string $name
     * @param string $key
     * @param int $iblockId
     * @param int $width
     * @param int $height
     * @param string $lang
     * @param string $iblockfix
     *
     * @return string
     */
    protected function getIblockElementSearchPopupJS(
        $name,
        $key,
        $iblockId = 0,
        $width = 900,
        $height = 700,
        $lang = LANGUAGE_ID,
        $iblockfix = 'y'
    ) {
        $params = [
            'lang'      => $lang,
            'IBLOCK_ID' => $iblockId,
            'n'         => $name,
            'k'         => $key,
            'iblockfix' => $iblockfix,
        ];
        if ($iblockId <= 0) {
            unset($params['IBLOCK_ID']);
        }

        return sprintf(
            "jsUtils.OpenWindow('%s', %d, %d);",
            '/bitrix/admin/iblock_element_search.php?' . htmlentities(http_build_query($params)),
            $width,
            $height
        );
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function checkFields(array $property, array $value)
    {
        throw new NotImplementedMethodException('checkFields', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getLength(array $property, array $value)
    {
        throw new NotImplementedMethodException('getLength', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function convertToDB(array $property, array $value)
    {
        throw new NotImplementedMethodException('convertToDB', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function convertFromDB(array $property, array $value)
    {
        throw new NotImplementedMethodException('convertFromDB', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getPropertyFieldHtml(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getPropertyFieldHtml', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getAdminListViewHTML(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getAdminListViewHTML', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getPublicViewHTML(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getPublicViewHTML', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getPublicEditHTML(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getPublicEditHTML', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getSearchContent(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getSearchContent', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function prepareSettings(array $property)
    {
        throw new NotImplementedMethodException('prepareSettings', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getSettingsHTML(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getSettingsHTML', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getPropertyFieldHtmlMulty(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getPropertyFieldHtmlMulty', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getAdminFilterHTML(array $property, array $control)
    {
        throw new NotImplementedMethodException('getAdminFilterHTML', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getPublicFilterHTML(array $property, array $control)
    {
        throw new NotImplementedMethodException('getPublicFilterHTML', static::class);
    }

    /**
     * @inheritdoc
     * @throws NotImplementedMethodException
     */
    public function getUIFilterProperty(array $property, $controlName, array &$filter)
    {
        throw new NotImplementedMethodException('getUIFilterProperty', static::class);
    }
}