<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties;

use Exception;
use CFile;
use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction\IblockPropertyTypeBase;

/**
 * Class YoutubeVideo
 * Кастомное поле типа Видео YouTube.
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties
 *
 * @since 17.10.2020
 */
class YoutubeVideo extends IblockPropertyTypeBase
{
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
        return 'Youtube видео';
    }

    /**
     * @inheritdoc
     */
    public function getCallbacksMapping()
    {
        return [
            'GetAdminListViewHTML' => [$this, 'getAdminListViewHTML'],
            'GetPropertyFieldHtml' => [$this, 'getPropertyFieldHtml'],
            'ConvertToDB' => [$this, 'convertToDB'],
            'ConvertFromDB' => [$this, 'convertFromDB'],
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
        $preview = null;
        if (!empty($value['VALUE']['PREVIEW'])) {
            $preview = CFile::GetPath($value['VALUE']['PREVIEW']);
        }

        $return =
            '<label>ID видео: <input type="text" name="'.$control['VALUE'].'[ID]" value="'.$value['VALUE']['ID'].'" /></label><br/>'.
            '<label>Название видео: <input type="text" name="'.$control['VALUE'].'[NAME]" value="'.$value['VALUE']['NAME'].'" /></label><br/>'.
            '<label>Превью картинка: <input type="hidden" name="'.$control['VALUE'].'[PREVIEW]" value="'.($preview !== null ? $preview : '').'" /><input type="file" name="'.$control['VALUE'].'[PREVIEW_FILE]"/></label>';

        if ($this->getControlMode($control) !== self::CONTROL_MODE_EDIT_FORM) {
            $return .= '<br><small>Текущее значение: '.$this->getHumanValueRepresentation($value).'</small>';
        }

        if (
            $this->getControlMode($control) !== self::CONTROL_MODE_EDIT_FORM
            && $property['WITH_DESCRIPTION'] === 'Y'
        ) {
            $return .= '<div><input type="text" size="'
                .$property['COL_COUNT']
                .'" name="'
                .$control['DESCRIPTION']
                .'" value="'
                .htmlspecialchars($value['DESCRIPTION'])
                .'" /></div>';
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function convertToDB(array $property, array $value)
    {
        if (!empty($value['VALUE'])) {
            if (!empty($value['VALUE']['PREVIEW_FILE'])) {
                //Удаление старого файла
                if ((int)$value['VALUE']['PREVIEW'] > 0) {
                    CFile::Delete((int)$value['VALUE']['PREVIEW']);
                }
                //сохранение нового файла
                $res = CFile::SaveFile($value['VALUE']['PREVIEW_FILE'], '/video', true);

                if (is_numeric($res)) {
                    $value['VALUE']['PREVIEW'] = (int)$res;
                } else {
                    /** @psalm-suppress InvalidGlobal */
                    global $APPLICATION;
                    $APPLICATION->ThrowException('Ошибка при сохранении превью видео');
                }
            }
            if (isset($value['VALUE']['PREVIEW_FILE'])) {
                unset($value['VALUE']['PREVIEW_FILE']);
            }

            $value['VALUE'] = serialize($value['VALUE']);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function convertFromDB(array $property, array $value)
    {
        if (!empty($value['VALUE'])) {
            $value['VALUE'] = unserialize($value['VALUE']);

            if ((int)$value['VALUE']['PREVIEW'] > 0) {
                try {
                    $value['VALUE']['PREVIEW'] = CFile::GetPath((int)$value['VALUE']['PREVIEW']);
                } catch (Exception $e) {
                    $value['VALUE']['PREVIEW'] = '';
                }
            }
        } else {
            $value['VALUE'] = [
                'ID' => '',
                'NAME' => '',
                'PREVIEW' => '',
            ];
        }

        return $value;
    }

    /**
     * Возвращает человеко-понятное представление значения.
     *
     * @param array $value Значения.
     *
     * @return string
     */
    protected function getHumanValueRepresentation(array $value)
    {
        $preview = null;
        $previewHtml = 'Отсутствует';
        if (!empty($value['VALUE']['PREVIEW'])) {
            $previewHtml = '<img src="'. $value['VALUE']['PREVIEW'] .'" width="100" height="100">';
        }

        return 'ID видео - '.$value['VALUE']['ID'].' / Название - '.$value['VALUE']['NAME'].' / <br/>Превью - '.$previewHtml;
    }
}