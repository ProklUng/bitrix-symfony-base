<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties;

use CFileMan;

/**
 * Class UFVisualEditor
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\CustomProperties
 *
 * @since 15.04.2021
 *
 * @see https://github.com/amensum/bx-ion-core/blob/master/classes/UFVisualEditor.php
 */
class UFVisualEditor
{
    public function GetUserTypeDescription(): ?array
    {
        return [
            'CLASS_NAME' => self::class,
            'BASE_TYPE' => 'string',
            'USER_TYPE_ID' => 'visual_editor_field',
            'DESCRIPTION' => 'Визуальный редактор'
        ];
    }

    public function GetDBColumnType(): ?string
    {
        return 'text';
    }

    public function GetEditFormHTML($arUserField, $arHtmlControl): ?string
    {
        ob_start();
        CFileMan::AddHTMLEditorFrame(
            $arHtmlControl['NAME'],
            $arHtmlControl['VALUE'],
            false,
            'html',
            ['height' => "120"]
        );
        return ob_get_clean();
    }
}
