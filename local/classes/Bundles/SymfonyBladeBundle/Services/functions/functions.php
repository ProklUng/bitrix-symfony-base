<?php

use Local\Bundles\SymfonyBladeBundle\Services\Providers\BladeProvider;

if (!function_exists('renderBladeTemplateNew')) {
    /**
     * Render blade template callback.
     *
     * @param $templateFile
     * @param $arResult
     * @param $arParams
     * @param $arLangMessages
     * @param $templateFolder
     * @param $parentTemplateFolder
     * @param $template
     *
     * @return void
     */
    function renderBladeTemplate(string $templateFile, $arResult, $arParams, $arLangMessages, $templateFolder, $parentTemplateFolder, $template)
    {
        Bitrix\Main\Localization\Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/template.php');

        $view = BladeProvider::getViewFactory();

        BladeProvider::addTemplateFolderToViewPaths($template->__folder);

        global $APPLICATION, $USER;

        echo $view->file($_SERVER['DOCUMENT_ROOT'].$templateFile, compact(
            'arParams',
            'arResult',
            'arLangMessages',
            'template',
            'templateFolder',
            'parentTemplateFolder',
            'APPLICATION',
            'USER'
        ))->render();

        $epilogue = $templateFolder.'/component_epilog.php';
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$epilogue)) {
            $component = $template->__component;
            $component->SetTemplateEpilog([
                'epilogFile'     => $epilogue,
                'templateName'   => $template->__name,
                'templateFile'   => $template->__file,
                'templateFolder' => $template->__folder,
                'templateData'   => false,
            ]);
        }

        BladeProvider::removeTemplateFolderFromViewPaths($template->__folder);
    }
}
