<?php

use Bitrix\Main\Localization\Loc;
use ProklUng\Module\Boilerplate\Module;
use ProklUng\Module\Boilerplate\ModuleUtilsTrait;

Loc::loadMessages(__FILE__);

class prokl_ckeditor extends CModule
{
    use ModuleUtilsTrait;

    public function __construct()
    {
        $arModuleVersion = [];

        include __DIR__.'/version.php';

        if (is_array($arModuleVersion)
            &&
            array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_FULL_NAME = 'ckeditor';
        $this->MODULE_VENDOR = 'prokl';
        $prefixLangCode = 'CKEDITOR';

        $this->MODULE_NAME = Loc::getMessage($prefixLangCode.'_MODULE_NAME');
        $this->MODULE_ID = $this->MODULE_VENDOR.'.'.$this->MODULE_FULL_NAME;

        $this->MODULE_DESCRIPTION = Loc::getMessage($prefixLangCode.'_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage($prefixLangCode.'_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage($prefixLangCode.'MODULE_PARTNER_URI');


        $this->moduleManager = new Module(
            [
                'MODULE_ID' => $this->MODULE_ID,
                'VENDOR_ID' => $this->MODULE_VENDOR,
                'MODULE_VERSION' => $this->MODULE_VERSION,
                'MODULE_VERSION_DATE' => $this->MODULE_VERSION_DATE,
                'ADMIN_FORM_ID' => $this->MODULE_VENDOR.'_settings_form',
            ]
        );

        $this->moduleManager->addModuleInstance($this);
        $this->options();
    }

    public function InstallEvents()
    {
        COption::SetOptionString('iblock', 'use_htmledit', 'N');

        RegisterModuleDependences(
            'main',
            'OnEpilog',
            'prokl.ckeditor',
            '\Prokl\Ckeditor\EventCK',
            'register'
        );
    }

    public function UnInstallEvents()
    {
        COption::SetOptionString('iblock', 'use_htmledit', 'Y');

        UnRegisterModuleDependences(
            'main',
            'OnEpilog',
            'prokl.ckeditor',
            '\Prokl\Ckeditor\EventCK',
            'register'
        );
    }

}
